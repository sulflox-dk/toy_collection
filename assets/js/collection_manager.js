const CollectionMgr = {
	// Variabler til at holde styr på tilstand
	currentPage: 1,
	baseUrl: '/',
	container: null,

	init: function () {
		console.log('CollectionMgr Init started');

		// Sæt Base URL sikkert (hvis App objektet findes)
		this.baseUrl =
			typeof App !== 'undefined' && App.baseUrl ? App.baseUrl : '/';
		this.container = document.getElementById('collectionGridContainer');

		// Element referencer til filtre (Inputs)
		this.search = document.getElementById('searchCollection');

		// --- GAMLE FILTRE ---
		this.fUniverse = document.getElementById('filterUniverse');
		this.fLine = document.getElementById('filterLine');
		this.fEntSource = document.getElementById('filterEntSource'); // I HTML hedder den ent_source i nogle versioner, tjek ID
		if (!this.fEntSource)
			this.fEntSource = document.getElementById('filterSource'); // Fallback hvis ID varierer

		this.fStorage = document.getElementById('filterStorage');
		this.fSource = document.getElementById('filterPurchaseSource');
		this.fStatus = document.getElementById('filterStatus');

		// --- NYE FILTRE ---
		this.fMan = document.getElementById('filterManufacturer');
		this.fType = document.getElementById('filterProductType');
		this.fComp = document.getElementById('filterCompleteness');
		this.fMissing = document.getElementById('filterMissingParts');
		this.fImg = document.getElementById('filterImage');

		// 1. Start lyttere på filtrene
		this.attachFilterListeners();

		// 2. Sæt knap-status baseret på cookie
		const match = document.cookie.match(
			new RegExp('(^| )collection_view_mode=([^;]+)'),
		);
		const currentMode = match ? match[2] : 'list';
		this.updateViewButtons(currentMode);

		// 3. GLOBAL CLICK LISTENER (Den robuste løsning)
		document.body.addEventListener('click', (e) => {
			// Hjælpefunktion: Find ID fra enten Card (div) eller Table Row (tr)
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

		// 4. Load indhold (KUN hvis vi er på Collection-siden hvor containeren findes)
		if (this.container) {
			// Check om vi har en gemt side i hukommelsen eller start på 1
			this.loadPage(1);
		}
	},

	// Skift visning (List/Cards)
	switchView: function (mode) {
		document.cookie =
			'collection_view_mode=' + mode + '; path=/; max-age=31536000'; // Gem i 1 år
		this.updateViewButtons(mode);

		// Genindlæs listen hvis vi er på collection siden
		if (this.container) {
			this.loadPage(this.currentPage || 1);
		} else {
			// Hvis vi er på dashboard eller andet sted, reload siden
			window.location.reload();
		}
	},

	// Opdater visuel status på knapperne
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

	// Håndter sletning
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

		// Vi bruger et POST kald til delete actionen i stedet for get_item_html hacket
		const formData = new FormData();
		formData.append('id', id);

		fetch(`${this.baseUrl}?module=Collection&controller=Toy&action=delete`, {
			method: 'POST',
			body: formData,
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					if (window.App && App.showToast)
						App.showToast('Item deleted successfully!');

					// Reload grid
					if (this.container) {
						this.loadPage(this.currentPage);
					} else {
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
			// Nye filtre:
			this.fMan,
			this.fType,
			this.fComp,
			this.fMissing,
			this.fImg,
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

		// Nulstil alle referencer vi kender
		const filters = [
			this.fUniverse,
			this.fLine,
			this.fEntSource,
			this.fStorage,
			this.fSource,
			this.fStatus,
			this.fMan,
			this.fType,
			this.fComp,
			this.fMissing,
			this.fImg,
		];

		filters.forEach((f) => {
			if (f) f.value = '';
		});

		this.loadPage(1);
	},

	loadPage: function (page) {
		this.currentPage = page;

		// Byg parametre
		const params = new URLSearchParams({
			module: 'Collection',
			controller: 'Toy',
			action: 'index',
			ajax_grid: 1,
			page: page,

			// Gamle
			universe_id: this.fUniverse ? this.fUniverse.value : '',
			line_id: this.fLine ? this.fLine.value : '',
			ent_source_id: this.fEntSource ? this.fEntSource.value : '',
			storage_id: this.fStorage ? this.fStorage.value : '',
			source_id: this.fSource ? this.fSource.value : '',
			status: this.fStatus ? this.fStatus.value : '',

			// Nye
			manufacturer_id: this.fMan ? this.fMan.value : '',
			product_type_id: this.fType ? this.fType.value : '',
			completeness: this.fComp ? this.fComp.value : '',
			missing_parts: this.fMissing ? this.fMissing.value : '', // Bemærk navn matcher PHP
			image_status: this.fImg ? this.fImg.value : '',

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
	// Opdaterer en enkelt række/kort uden at reloade hele siden
	refreshItem: function (id) {
		console.log('CollectionMgr: Refreshing item', id);

		// RETTELSE: Vi bruger en specifik selector for at undgå at ramme inputs i modalen
		// Vi leder kun efter .toy-card (grid view) eller tr (list view)
		let oldEl = null;

		// Hvis vi har en container, søg i den først (sikrest)
		if (this.container) {
			oldEl = this.container.querySelector(`[data-id="${id}"]`);
		}

		// Fallback (hvis vi er på dashboard eller container ikke er sat)
		if (!oldEl) {
			oldEl = document.querySelector(
				`.toy-card[data-id="${id}"], tr[data-id="${id}"]`,
			);
		}

		// Hvis kortet slet ikke findes (f.eks. nyt item), stopper vi bare her
		if (!oldEl) {
			console.log(
				'CollectionMgr: Item not found in grid (might be new). Skipping refresh.',
			);
			return;
		}

		oldEl.style.opacity = '0.5';

		// Cache busting med &t=...
		const url = `${this.baseUrl}?module=Collection&controller=Toy&action=get_item_html&id=${id}&t=${new Date().getTime()}`;

		fetch(url)
			.then((res) => res.text())
			.then((html) => {
				const temp = document.createElement('div');
				temp.innerHTML = html;

				// 1. Prøv at finde elementet specifikt inde i svaret
				let newEl = temp.querySelector(`[data-id="${id}"]`);

				// 2. Hvis ikke fundet, brug første child
				if (!newEl && temp.firstElementChild) {
					newEl = temp.firstElementChild;
				}

				if (newEl) {
					oldEl.replaceWith(newEl);

					// Flash effekt
					newEl.style.transition = 'background-color 0.5s ease';
					const isRow = newEl.tagName === 'TR';
					const flashColor = isRow ? '#f8f9fa' : '#e8f5e9';

					const originalBg = newEl.style.backgroundColor;
					newEl.style.backgroundColor = flashColor;

					setTimeout(() => {
						newEl.style.backgroundColor = originalBg || '';
					}, 800);

					console.log('CollectionMgr: Refresh success!');
				} else {
					console.error(
						'CollectionMgr: Kunne ikke finde det nye element i svaret.',
					);
					oldEl.style.opacity = '1';
				}
			})
			.catch((err) => {
				console.error('Refresh failed:', err);
				oldEl.style.opacity = '1';
			});
	},
};

document.addEventListener('DOMContentLoaded', () => {
	CollectionMgr.init();
});
