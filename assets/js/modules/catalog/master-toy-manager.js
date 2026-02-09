const MasterToyMgr = {
	// Variabler
	currentPage: 1,
	baseUrl: '/',
	container: null,

	// Cache til subjects data (bruges i modal og multi-add)
	allSubjects: [],

	init: function () {
		console.log('MasterToyMgr Init started');

		this.baseUrl =
			typeof App !== 'undefined' && App.baseUrl ? App.baseUrl : '/';
		this.container = document.getElementById('masterToyGridContainer');
		this.search = document.getElementById('searchName');

		// Referencer til ALLE filtre
		this.filterUni = document.getElementById('filterUniverse');
		this.filterMan = document.getElementById('filterManufacturer'); // NY
		this.filterLine = document.getElementById('filterLine');
		this.filterType = document.getElementById('filterProductType'); // NY
		this.filterSource = document.getElementById('filterSource');
		this.filterOwned = document.getElementById('filterOwned');
		this.filterImage = document.getElementById('filterImage'); // NY

		// Array af filtre
		const filters = [
			this.filterUni,
			this.filterMan,
			this.filterLine,
			this.filterType,
			this.filterSource,
			this.filterOwned,
			this.filterImage,
		];

		// Tilføj lyttere
		filters.forEach((f) => {
			if (f) f.addEventListener('change', () => this.loadPage(1));
		});

		// Search delay
		let timeout;
		if (this.search) {
			this.search.addEventListener('keyup', () => {
				clearTimeout(timeout);
				timeout = setTimeout(() => this.loadPage(1), 400);
			});
		}

		// Set View Button State
		const match = document.cookie.match(
			new RegExp('(^| )catalog_view_mode=([^;]+)'),
		);
		const currentMode = match ? match[2] : 'list';
		this.updateViewButtons(currentMode);

		// Global Click Listener
		document.body.addEventListener('click', (e) => {
			const findId = (el) => {
				const container = el.closest('[data-id]') || el.closest('tr');
				return container ? container.dataset.id : null;
			};

			const editBtn = e.target.closest('.btn-edit');
			if (editBtn) {
				e.preventDefault();
				const id = findId(editBtn);
				if (id) this.openEditModal(id);
				return;
			}

			const mediaBtn = e.target.closest('.btn-media');
			if (mediaBtn) {
				e.preventDefault();
				const id = findId(mediaBtn);
				if (id) this.openMedia(id);
				return;
			}

			const delBtn = e.target.closest('.btn-delete');
			if (delBtn) {
				e.preventDefault();
				this.handleDelete(delBtn);
				return;
			}
		});

		// Mutation Observer
		const modalEl = document.getElementById('appModal');
		if (modalEl) {
			const observer = new MutationObserver(() => {
				const form = document.getElementById('masterToyForm');
				if (form && !form.dataset.initialized) {
					form.dataset.initialized = 'true';
					this.initForm();
				}
			});
			observer.observe(modalEl, { childList: true, subtree: true });
		}

		if (this.container) {
			this.currentPage = 1;
		}
	},

	// --- VIEW SWITCHING ---
	switchView: function (mode) {
		document.cookie =
			'catalog_view_mode=' + mode + '; path=/; max-age=31536000';
		this.updateViewButtons(mode);

		// Genindl�s listen hvis vi er p� index-siden
		if (this.container) {
			this.loadPage(this.currentPage || 1);
		} else {
			window.location.reload();
		}
	},

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

	loadPage: function (page) {
		this.currentPage = page;

		const params = new URLSearchParams({
			module: 'Catalog',
			controller: 'MasterToy',
			action: 'index',
			ajax_grid: 1,
			page: page,
			universe_id: this.filterUni ? this.filterUni.value : '',
			manufacturer_id: this.filterMan ? this.filterMan.value : '', // NY
			line_id: this.filterLine ? this.filterLine.value : '',
			product_type_id: this.filterType ? this.filterType.value : '', // NY
			source_id: this.filterSource ? this.filterSource.value : '',
			owned_status: this.filterOwned ? this.filterOwned.value : '',
			image_status: this.filterImage ? this.filterImage.value : '', // NY
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
					console.error('Load Error:', err);
					this.container.style.opacity = '1';
				});
		}
	},

	resetFilters: function () {
		if (this.filterUni) this.filterUni.value = '';
		if (this.filterMan) this.filterMan.value = '';
		if (this.filterLine) this.filterLine.value = '';
		if (this.filterType) this.filterType.value = '';
		if (this.filterSource) this.filterSource.value = '';
		if (this.filterOwned) this.filterOwned.value = '';
		if (this.filterImage) this.filterImage.value = '';
		if (this.search) this.search.value = '';

		this.loadPage(1);
	},

	// --- ACTIONS ---
	openUniverseSelect: function () {
		App.openModal('Catalog', 'MasterToy', 'modal_step1');
	},

	goToStep2: function (universeId) {
		App.openModal('Catalog', 'MasterToy', 'modal_step2', {
			universe_id: universeId,
		});
	},

	openEditModal: function (id) {
		App.openModal('Catalog', 'MasterToy', 'modal_step2', { id: id });
	},

	// �bner Media Modal
	openMedia: function (id, mode = 'edit') {
		const modalEl = document.getElementById('appModal');
		const modalBody = modalEl.querySelector('.modal-content');

		// Vis modal (hvis den ikke allerede er �ben)
		const bsModal = new bootstrap.Modal(modalEl);
		bsModal.show();

		// Vis loading spinner
		modalBody.innerHTML =
			'<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

		fetch(
			`${this.baseUrl}?module=Catalog&controller=MasterToy&action=modal_media&id=${id}&mode=${mode}`,
		)
			.then((res) => res.text())
			.then((html) => {
				modalBody.innerHTML = html;

				// Initialiser Media Uploader scriptet hvis det findes
				if (App.initMediaUploads) {
					App.initMediaUploads();
				} else {
					console.error(
						'App.initMediaUploads not found. Is collection-media.js loaded?',
					);
				}
			});
	},

	handleDelete: function (btn) {
		if (!confirm('Delete this Master Toy? This cannot be undone.')) return;

		const container = btn.closest('[data-id]') || btn.closest('tr');
		const id = container ? container.dataset.id : null;

		if (!id) return;

		// Visuel feedback
		container.style.opacity = '0.3';

		const formData = new FormData();
		formData.append('id', id);

		fetch(
			`${this.baseUrl}?module=Catalog&controller=MasterToy&action=delete`,
			{ method: 'POST', body: formData },
		)
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					if (window.App) App.showToast('Toy deleted successfully!');
					this.loadPage(this.currentPage);
				} else {
					container.style.opacity = '1';
					alert('Error: ' + data.error);
				}
			})
			.catch((err) => {
				container.style.opacity = '1';
				console.error(err);
			});
	},

	// --- FORM LOGIC (Trin 2) ---
	initForm: function () {
		console.log('Initializing Master Toy Form...');

		this.initStep2Listeners();

		const container = document.getElementById('itemsContainer');
		const template = document.getElementById('itemRowTemplate');

		if (!container || !template) return;

		let items = [];
		try {
			items = JSON.parse(container.dataset.items || '[]');
			this.allSubjects = JSON.parse(container.dataset.subjects || '[]');
		} catch (e) {
			console.error('JSON parse error', e);
		}

		container.innerHTML = '';
		items.forEach((item) => this.renderRow(item));
		this.updateUI();
	},

	initStep2Listeners: function () {
		const uniSelect = document.getElementById('master_toy_universe_id');
		const manSelect = document.getElementById('master_toy_manufacturer_id');
		const lineSelect = document.getElementById('master_toy_toy_line_id');

		if (!uniSelect || !manSelect || !lineSelect) return;

		uniSelect.addEventListener('change', function () {
			const universeId = this.value;
			manSelect.innerHTML = '<option value="">Loading...</option>';
			lineSelect.innerHTML =
				'<option value="">Select Manufacturer first...</option>';

			if (universeId) {
				fetch(
					`${App.baseUrl}?module=Catalog&controller=Manufacturer&action=get_json&universe_id=${universeId}`,
				)
					.then((res) => res.json())
					.then((data) => {
						let html = '<option value="">Select Manufacturer...</option>';
						data.forEach((item) => {
							html += `<option value="${item.id}">${item.name}</option>`;
						});
						manSelect.innerHTML = html;

						// Auto-select hvis kun 1 mulighed
						if (data.length === 1) {
							manSelect.value = data[0].id;
							manSelect.dispatchEvent(new Event('change'));
						}
					});
			} else {
				manSelect.innerHTML =
					'<option value="">Select Universe...</option>';
			}
		});

		manSelect.addEventListener('change', function () {
			const manufacturerId = this.value;
			lineSelect.innerHTML = '<option value="">Loading...</option>';

			if (manufacturerId) {
				fetch(
					`${App.baseUrl}?module=Catalog&controller=ToyLine&action=get_json&manufacturer_id=${manufacturerId}`,
				)
					.then((res) => res.json())
					.then((data) => {
						let html = '<option value="">Select Toy Line...</option>';
						data.forEach((item) => {
							html += `<option value="${item.id}">${item.name}</option>`;
						});
						lineSelect.innerHTML = html;

						if (data.length === 1) {
							lineSelect.value = data[0].id;
						}
					});
			} else {
				lineSelect.innerHTML =
					'<option value="">Select Manufacturer first...</option>';
			}
		});
	},

	// ... (resten af koden før submitForm) ...

	submitForm: function () {
		const form = document.getElementById('masterToyForm');
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}

		const formData = new FormData(form);
		const id = formData.get('id');
		const action = id ? 'update' : 'create';

		const btn = form.querySelector('button[onclick*="submitForm"]');
		let originalText = '';
		if (btn) {
			originalText = btn.innerHTML;
			btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
			btn.disabled = true;
		}

		fetch(
			`${this.baseUrl}?module=Catalog&controller=MasterToy&action=${action}`,
			{
				method: 'POST',
				body: formData,
			},
		)
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					const modalEl = document.getElementById('appModal');
					const modal = bootstrap.Modal.getInstance(modalEl);

					if (document.activeElement) {
						document.activeElement.blur();
					}

					if (modal) modal.hide();

					App.showToast(
						id
							? 'Toy updated successfully!'
							: 'Toy created successfully!',
					);

					// --- SMART REFRESH LOGIK HER ---
					if (id) {
						// Hvis det er en opdatering: Refresh kun kortet
						this.refreshItem(id);
					} else {
						// Hvis det er en ny: Reload side 1
						this.loadPage(1);
						// Hop til media upload hvis ny
						setTimeout(() => {
							MasterToyMgr.openMedia(data.id, 'create');
						}, 500);
					}
					// -------------------------------
				} else {
					alert(data.error);
					if (btn) {
						btn.innerHTML = originalText;
						btn.disabled = false;
					}
				}
			})
			.catch((err) => {
				console.error(err);
				alert('An error occurred.');
				if (btn) {
					btn.innerHTML = originalText;
					btn.disabled = false;
				}
			});
	},

	// --- NY FUNKTION: Opdaterer et enkelt kort i listen ---
	refreshItem: function (id) {
		console.log('MasterToyMgr: Refreshing item', id);
		const oldCard = document.querySelector(`.toy-card[data-id="${id}"]`);

		if (!oldCard) {
			console.warn('Old card not found:', id);
			return;
		}

		oldCard.style.opacity = '0.5';

		const url = `${this.baseUrl}?module=Catalog&controller=MasterToy&action=get_item_html&id=${id}&t=${new Date().getTime()}`;

		fetch(url)
			.then((res) => res.text())
			.then((html) => {
				const temp = document.createElement('div');
				temp.innerHTML = html;

				// 1. Prøv at finde kortet direkte (hvis serveren returnerer row -> col -> card)
				let newCard = temp.querySelector(`.toy-card[data-id="${id}"]`);

				// 2. Hvis ikke fundet, tjek om hele HTML'en ER kortet
				if (
					!newCard &&
					temp.firstElementChild &&
					temp.firstElementChild.classList.contains('toy-card')
				) {
					newCard = temp.firstElementChild;
				}

				if (newCard) {
					oldCard.replaceWith(newCard);

					// Flash effekt
					newCard.style.transition = 'background-color 0.5s ease';
					const originalBg = newCard.style.backgroundColor;
					newCard.style.backgroundColor = '#e8f5e9';
					setTimeout(() => {
						newCard.style.backgroundColor = originalBg || '';
					}, 800);

					console.log('MasterToyMgr: Refresh success!');
				} else {
					console.error(
						'MasterToyMgr: Kunne ikke finde det nye kort i svaret.',
					);
					console.log('Server response:', html);
					oldCard.style.opacity = '1';
				}
			})
			.catch((err) => {
				console.error(err);
				oldCard.style.opacity = '1';
			});
	},

	// ... (resten af filen: addItem, removeItem osv.) ...

	// --- ITEM ROW / MULTI ADD LOGIC ---
	addItem: function () {
		this.renderRow({ quantity: 1 });
		this.updateUI();
		const container = document.getElementById('itemsContainer');
		if (container)
			setTimeout(() => (container.scrollTop = container.scrollHeight), 50);
	},

	removeItem: function (btn) {
		const row = btn.closest('.item-row');
		if (row) {
			row.remove();
			this.updateUI();
		}
	},

	renderRow: function (item) {
		const container = document.getElementById('itemsContainer');
		const template = document.getElementById('itemRowTemplate');

		const clone = template.content.cloneNode(true);
		const row = clone.querySelector('.item-row');

		const uid = Date.now() + Math.floor(Math.random() * 1000);

		// 1. Opdater navne (UID -> unikt ID)
		row.querySelectorAll('[name*="UID"]').forEach((el) => {
			el.name = el.name.replace('UID', uid);
		});

		// 2. Sæt ID (hvis det er en eksisterende række)
		// Vi bruger nu feltet direkte fra templaten (.input-id)
		const idInput = row.querySelector('.input-id');
		if (idInput && item.id) {
			idInput.value = item.id;
		}

		// 3. Udfyld øvrige felter
		const variantText = item.variant_description || '';
		row.querySelector('.input-variant').value = variantText;

		if (item.quantity) row.querySelector('.input-qty').value = item.quantity;

		// 4. Håndter Subject visning
		const subjectInput = row.querySelector('.input-subject-id');
		const displayCard = row.querySelector('.subject-display-card');

		if (item.subject_id) {
			subjectInput.value = item.subject_id;
			const subject = this.allSubjects.find((s) => s.id == item.subject_id);
			if (subject) {
				this.updateSubjectDisplay(displayCard, subject);
			} else if (item.subject_name) {
				this.updateSubjectDisplay(displayCard, {
					name: item.subject_name,
					type: item.subject_type || 'Item',
					faction: '',
				});
			}
		}

		container.appendChild(row);
	},

	updateUI: function () {
		const container = document.getElementById('itemsContainer');
		const badge = document.getElementById('itemCountBadge');
		if (container && badge) {
			const count = container.querySelectorAll('.item-row').length;
			badge.textContent = count + ' item(s)';
		}
	},

	// --- SEARCH / DROPDOWN LOGIC FOR ITEMS ---
	toggleSearch: function (cardEl) {
		const wrapper = cardEl.closest('.subject-selector-wrapper');
		const dropdown = wrapper.querySelector('.subject-search-dropdown');
		const input = dropdown.querySelector('.search-input');

		document.querySelectorAll('.subject-search-dropdown').forEach((el) => {
			if (el !== dropdown) el.classList.add('d-none');
		});

		const isHidden = dropdown.classList.contains('d-none');
		if (isHidden) {
			dropdown.classList.remove('d-none');
			input.value = '';
			input.focus();
			this.filterSubjects(input);

			setTimeout(() => {
				const closeHandler = (e) => {
					if (!wrapper.contains(e.target)) {
						dropdown.classList.add('d-none');
						document.removeEventListener('click', closeHandler);
					}
				};
				document.addEventListener('click', closeHandler);
			}, 0);
		} else {
			dropdown.classList.add('d-none');
		}
	},

	filterSubjects: function (inputEl) {
		const term = inputEl.value.toLowerCase();
		const listEl = inputEl
			.closest('.subject-search-dropdown')
			.querySelector('.results-list');

		const matches = this.allSubjects
			.filter((s) => {
				return (
					s.name.toLowerCase().includes(term) ||
					(s.type && s.type.toLowerCase().includes(term))
				);
			})
			.slice(0, 50);

		if (matches.length === 0) {
			listEl.innerHTML =
				'<div class="p-2 text-muted small text-center">No matches found</div>';
			return;
		}

		let html = '';
		matches.forEach((s) => {
			const metaParts = [];
			if (s.type) metaParts.push(s.type);
			if (s.faction) metaParts.push(s.faction);
			const meta = metaParts.join(' &bull; ');

			html += `
                <div class="subject-result-item" onclick="MasterToyMgr.selectSubject(this, ${s.id})">
                    <div class="fw-bold text-dark">${s.name}</div>
                    <div class="text-muted small">${meta}</div>
                </div>
            `;
		});
		listEl.innerHTML = html;
	},

	selectSubject: function (itemEl, id) {
		const wrapper = itemEl.closest('.subject-selector-wrapper');
		const input = wrapper.parentNode.querySelector('.input-subject-id');
		const displayCard = wrapper.querySelector('.subject-display-card');
		const dropdown = wrapper.querySelector('.subject-search-dropdown');

		input.value = id;

		const subject = this.allSubjects.find((s) => s.id == id);
		if (subject) {
			this.updateSubjectDisplay(displayCard, subject);
		}

		dropdown.classList.add('d-none');
	},

	updateSubjectDisplay: function (cardEl, subject) {
		const nameEl = cardEl.querySelector('.subject-name');
		const metaEl = cardEl.querySelector('.subject-meta');
		const iconEl = cardEl.querySelector('.subject-icon');

		nameEl.textContent = subject.name;
		nameEl.classList.remove('text-muted');

		const metaParts = [];
		if (subject.type) metaParts.push(subject.type);
		if (subject.faction) metaParts.push(subject.faction);
		metaEl.innerHTML = metaParts.join(' &bull; ');
		metaEl.style.display = 'block';

		if (subject.type === 'Character')
			iconEl.className = 'fas fa-user subject-icon';
		else if (subject.type === 'Vehicle')
			iconEl.className = 'fas fa-fighter-jet subject-icon';
		else if (subject.type === 'Creature')
			iconEl.className = 'fas fa-dragon subject-icon';
		else iconEl.className = 'fas fa-cube subject-icon';
	},

	// --- MULTI ADD MODAL ---
	openMultiAdd: function () {
		const overlay = document.getElementById('multiAddOverlay');
		const input = document.getElementById('multiAddSearch');
		const list = document.getElementById('multiAddList');

		if (!overlay) return;

		overlay.classList.remove('d-none');
		input.value = '';
		input.focus();
		list.innerHTML =
			'<div class="text-center text-muted mt-5">Type to search for subjects...</div>';
		this.updateMultiCount();
	},

	closeMultiAdd: function () {
		const overlay = document.getElementById('multiAddOverlay');
		if (overlay) overlay.classList.add('d-none');
	},

	filterMultiList: function (term) {
		const list = document.getElementById('multiAddList');
		term = term.toLowerCase();

		if (term.length < 2) {
			if (term.length === 0)
				list.innerHTML =
					'<div class="text-center text-muted mt-5">Type to search for subjects...</div>';
			return;
		}

		const matches = this.allSubjects
			.filter((s) => {
				return (
					s.name.toLowerCase().includes(term) ||
					(s.type && s.type.toLowerCase().includes(term))
				);
			})
			.slice(0, 100);

		if (matches.length === 0) {
			list.innerHTML =
				'<div class="text-center text-muted mt-3">No matches found.</div>';
			return;
		}

		let html = '<div class="list-group list-group-flush">';
		matches.forEach((s) => {
			const metaParts = [];
			if (s.type) metaParts.push(s.type);
			if (s.faction) metaParts.push(s.faction);

			let iconClass = 'fas fa-cube';
			if (s.type === 'Character') iconClass = 'fas fa-user';
			else if (s.type === 'Packaging') iconClass = 'fas fa-box-open';
			else if (s.type === 'Accessory') iconClass = 'fas fa-wrench';

			html += `
                <label class="list-group-item d-flex gap-3 align-items-center" style="cursor:pointer;">
                    <input class="form-check-input flex-shrink-0" type="checkbox" value="${s.id}" style="width: 1.3em; height: 1.3em;" onchange="MasterToyMgr.updateMultiCount()">
                    <div class="d-flex align-items-center w-100 justify-content-between">
                        <div>
                            <div class="fw-bold text-dark mb-0">${s.name}</div>
                            <small class="text-muted">${metaParts.join(' ? ')}</small>
                        </div>
                        <i class="${iconClass} text-muted opacity-25 fs-4"></i>
                    </div>
                </label>
            `;
		});
		html += '</div>';
		list.innerHTML = html;
	},

	updateMultiCount: function () {
		const countSpan = document.getElementById('multiAddCount');
		const checked = document.querySelectorAll(
			'#multiAddList input[type="checkbox"]:checked',
		).length;
		if (countSpan) countSpan.textContent = checked + ' selected';
	},

	addSelectedItems: function () {
		const checkboxes = document.querySelectorAll(
			'#multiAddList input[type="checkbox"]:checked',
		);

		if (checkboxes.length === 0) {
			alert('Please select at least one item.');
			return;
		}

		checkboxes.forEach((cb) => {
			const subjectId = parseInt(cb.value);
			const subject = this.allSubjects.find((s) => s.id === subjectId);

			if (subject) {
				this.renderRow({
					subject_id: subject.id,
					subject_name: subject.name,
					subject_type: subject.type,
					quantity: 1,
					variant_description: '',
				});
			}
		});

		this.updateUI();
		this.closeMultiAdd();
		const container = document.getElementById('itemsContainer');
		if (container)
			setTimeout(() => (container.scrollTop = container.scrollHeight), 100);

		App.showToast(checkboxes.length + ' items added!');
	},

	// ... inde i MasterToyMgr objektet ...

	quickAdd: function (id) {
		// Vi åbner Collection -> Toy -> form (Create modalen)
		// Vi sender master_toy_id med, så controlleren ved hvad vi vil
		// Vi sender auto_fill=1 med, så JS ved at den skal tilføje alle items
		App.openModal('Collection', 'Toy', 'form', {
			master_toy_id: id,
			auto_fill: 1,
		});
	},

	// ...
};

document.addEventListener('DOMContentLoaded', () => {
	MasterToyMgr.init();
});
