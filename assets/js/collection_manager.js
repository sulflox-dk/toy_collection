const CollectionMgr = {
	// Variabler til at holde styr p� tilstand
	currentPage: 1,
	baseUrl: '/',
	container: null,

	init: function () {
		// S�t Base URL sikkert (hvis App objektet findes)
		this.baseUrl =
			typeof App !== 'undefined' && App.baseUrl ? App.baseUrl : '/';
		this.container = document.getElementById('collectionGridContainer');

		// Element referencer til filtre
		this.search = document.getElementById('searchCollection');
		this.fUniverse = document.getElementById('filterUniverse');
		this.fLine = document.getElementById('filterLine');
		this.fEntSource = document.getElementById('filterEntSource');
		this.fStorage = document.getElementById('filterStorage');
		this.fSource = document.getElementById('filterPurchaseSource');
		this.fStatus = document.getElementById('filterStatus');

		// 1. Start lyttere p� filtrene (hvis de findes)
		this.attachFilterListeners();

		// 2. S�t knap-status baseret p� cookie
		const match = document.cookie.match(
			new RegExp('(^| )collection_view_mode=([^;]+)'),
		);
		const currentMode = match ? match[2] : 'list';
		this.updateViewButtons(currentMode);

		// 3. GLOBAL CLICK LISTENER (Den robuste l�sning)
		// Vi lytter p� hele dokumentet, s� vi fanger klik fra b�de AJAX-indhold og statisk indhold
		document.body.addEventListener('click', (e) => {
			// Hj�lpefunktion: Find ID fra enten Card (div) eller Table Row (tr)
			const findId = (el) => {
				const container = el.closest('[data-id]') || el.closest('tr');
				return container ? container.dataset.id : null;
			};

			// --- DELETE KNAP ---
			const delBtn = e.target.closest('.btn-delete');
			if (delBtn) {
				e.preventDefault();
				this.handleDelete(delBtn);
				return;
			}

			// --- EDIT KNAP ---
			const editBtn = e.target.closest('.btn-edit');
			if (editBtn) {
				e.preventDefault();
				const id = findId(editBtn);
				if (id && window.CollectionForm) {
					CollectionForm.openEditModal(id);
				}
				return;
			}

			// --- MEDIA KNAP ---
			const mediaBtn = e.target.closest('.btn-media');
			if (mediaBtn) {
				e.preventDefault();
				const id = findId(mediaBtn);
				if (id && window.CollectionForm) {
					CollectionForm.openMediaModal(id);
				}
				return;
			}
		});

		// 4. Load indhold (KUN hvis vi er p� Collection-siden hvor containeren findes)
		if (this.container) {
			this.loadPage(1);
		}
	},

	// Skift visning (List/Cards)
	switchView: function (mode) {
		document.cookie =
			'collection_view_mode=' + mode + '; path=/; max-age=31536000'; // Gem i 1 �r
		this.updateViewButtons(mode);
		// Genindl�s listen hvis vi er p� collection siden
		if (this.container) {
			this.loadPage(this.currentPage || 1);
		} else {
			// Hvis vi er p� dashboard eller andet sted, reload siden
			window.location.reload();
		}
	},

	// Opdater visuel status p� knapperne
	updateViewButtons: function (mode) {
		const btnList = document.getElementById('btn-view-list');
		const btnCards = document.getElementById('btn-view-cards');

		if (btnList && btnCards) {
			if (mode === 'list') {
				btnList.classList.add('active', 'bg-secondary', 'text-white');
				btnCards.classList.remove('active', 'bg-secondary', 'text-white');
			} else {
				btnCards.classList.add('active', 'bg-secondary', 'text-white');
				btnList.classList.remove('active', 'bg-secondary', 'text-white');
			}
		}
	},

	// H�ndter sletning
	handleDelete: function (btn) {
		if (
			!confirm(
				'Are you sure? This will delete the toy and all associated images permanently.',
			)
		) {
			return;
		}

		const container = btn.closest('[data-id]') || btn.closest('tr');
		const id = container ? container.dataset.id : null;

		if (!id) return;

		// Visuel feedback
		container.style.opacity = '0.3';

		fetch(`${this.baseUrl}?module=Collection&controller=Toy&action=delete`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: `id=${id}`,
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					if (window.App && App.showToast)
						App.showToast('Item deleted successfully!');

					// Hvis vi er p� collection siden -> reload grid via ajax
					if (this.container) {
						this.loadPage(this.currentPage);
					} else {
						// Hvis vi er p� dashboard -> reload hele siden
						window.location.reload();
					}
				} else {
					container.style.opacity = '1';
					alert('Error deleting: ' + (data.error || 'Unknown error'));
				}
			})
			.catch((err) => {
				container.style.opacity = '1';
				console.error(err);
				alert('System error occurred.');
			});
	},

	attachFilterListeners: function () {
		const filters = [
			this.fUniverse,
			this.fLine,
			this.fEntSource,
			this.fStorage,
			this.fSource,
			this.fStatus,
		];

		filters.forEach((f) => {
			if (f) f.addEventListener('change', () => this.loadPage(1));
		});

		let timeout;
		if (this.search) {
			this.search.addEventListener('keyup', () => {
				clearTimeout(timeout);
				timeout = setTimeout(() => this.loadPage(1), 400);
			});
		}
	},

	resetFilters: function () {
		if (this.search) this.search.value = '';
		// Nulstil alle selects i headeren
		const selects = document.querySelectorAll('.card-header select');
		selects.forEach((s) => (s.value = ''));
		this.loadPage(1);
	},

	loadPage: function (page) {
		this.currentPage = page;

		const params = new URLSearchParams({
			module: 'Collection',
			controller: 'Toy',
			action: 'index',
			ajax_grid: 1,
			page: page,
			universe_id: this.fUniverse ? this.fUniverse.value : '',
			line_id: this.fLine ? this.fLine.value : '',
			ent_source_id: this.fEntSource ? this.fEntSource.value : '',
			storage_id: this.fStorage ? this.fStorage.value : '',
			source_id: this.fSource ? this.fSource.value : '',
			status: this.fStatus ? this.fStatus.value : '',
			search: this.search ? this.search.value : '',
		});

		if (this.container) {
			this.container.style.opacity = '0.5';
			fetch(`${this.baseUrl}?${params.toString()}`)
				.then((res) => res.text())
				.then((html) => {
					this.container.innerHTML = html;
					this.container.style.opacity = '1';
				})
				.catch((err) => {
					console.error('Load failed:', err);
					this.container.innerHTML =
						'<div class="alert alert-danger p-3">Failed to load data.</div>';
					this.container.style.opacity = '1';
				});
		}
	},

	// Opdaterer en enkelt række/kort uden at reloade hele siden
	refreshItem: function (id) {
		// Find det eksisterende element
		// (data-id sidder på <tr> i tabel og .card i card view)
		const oldEl = document.querySelector(`[data-id="${id}"]`);

		if (!oldEl) {
			console.warn('Could not find element to refresh for ID:', id);
			return;
		}

		// Visuel feedback: Gør den lidt gennemsigtig mens vi henter
		oldEl.style.opacity = '0.5';

		fetch(
			`${this.baseUrl}?module=Collection&controller=Toy&action=get_item_html&id=${id}`,
		)
			.then((res) => res.text())
			.then((html) => {
				// Vi skaber en midlertidig container for at parse HTML'en
				const temp = document.createElement('div');
				temp.innerHTML = html;

				// Find det nye element inde i svaret
				const newEl = temp.querySelector(`[data-id="${id}"]`);

				if (newEl) {
					// ERSTAT DET GAMLE MED DET NYE
					oldEl.replaceWith(newEl);

					// Flash effekt (Gul baggrund i 1 sek)
					newEl.style.transition = 'background-color 0.5s ease';
					const originalBg = newEl.style.backgroundColor;
					newEl.style.backgroundColor = '#fff3cd'; // Bootstrap warning color (lys gul)

					setTimeout(() => {
						newEl.style.backgroundColor = originalBg || '';
					}, 800);
				} else {
					console.error('New element structure not found in response');
					// Fallback: Hvis noget gik galt, reload hele siden
					// window.location.reload();
				}
			})
			.catch((err) => {
				console.error('Refresh failed:', err);
				oldEl.style.opacity = '1'; // Reset opacity ved fejl
			});
	},
};

document.addEventListener('DOMContentLoaded', () => {
	CollectionMgr.init();

	// Hack til reload efter save (Hvis CollectionForm bruges)
	if (window.CollectionForm) {
		window.CollectionForm.handleSaveSuccess = function (data) {
			if (window.App && App.showToast) App.showToast('Saved successfully!');

			// Hvis container findes, reload kun grid. Ellers reload side (dashboard).
			if (document.getElementById('collectionGridContainer')) {
				CollectionMgr.loadPage(CollectionMgr.currentPage || 1);
			} else {
				window.location.reload();
			}
		};
	}
});
