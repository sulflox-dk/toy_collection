/**
 * COLLECTION FORM
 * Håndterer dropdowns, widget søgning og tilføjelse af parts
 */
App.initDependentDropdowns = function () {
	console.log('Initializing Toy Form logic...');

	const universeSelect = document.getElementById('selectUniverse');
	const manufacturerSelect = document.getElementById('selectManufacturer');
	const lineSelect = document.getElementById('selectLine');

	// WIDGET ELEMENTS
	const widgetInput = document.getElementById('inputMasterToyId');
	const widgetWrapper = document.getElementById('masterToyWidgetWrapper');
	const widgetCard = document.getElementById('masterToyDisplayCard');
	const widgetOverlay = document.getElementById('masterToyOverlay');
	const widgetSearch = document.getElementById('inputToySearch');
	const widgetList = document.getElementById('toyResultsList');

	const btnAddItem = document.getElementById('btnAddItemRow');
	const container = document.getElementById('childItemsContainer');
	const template = document.getElementById('childRowTemplate');
	const countBadge = document.getElementById('itemCountBadge');

	let availableMasterToyItems = [];
	let currentMasterToysList = [];
	let rowCount = 0;

	// Hent data fra containeren
	if (container && container.dataset.masterToyItems) {
		try {
			availableMasterToyItems = JSON.parse(container.dataset.masterToyItems);
		} catch (e) {
			console.error('JSON parse error', e);
		}
	}

	// --- WIDGET HELPERS ---

	const updateWidgetDisplay = (toy) => {
		const iconEl = document.getElementById('displayToyImgIcon');
		const imgEl = document.getElementById('displayToyImg');

		document.getElementById('displayToyTitle').textContent = toy
			? toy.name
			: 'Select Toy...';

		if (toy) {
			// Tekst
			const line2 = [toy.release_year, toy.type_name]
				.filter(Boolean)
				.join(' - ');
			document.getElementById('displayToyMeta1').textContent = line2;

			const sourceText =
				toy.source_material_name ||
				(toy.wave_number ? `Wave: ${toy.wave_number}` : '');
			document.getElementById('displayToyMeta2').textContent = sourceText;

			// Billede
			if (toy.image_path) {
				imgEl.src = toy.image_path;
				imgEl.classList.remove('d-none');
				if (iconEl) iconEl.classList.add('d-none');
			} else {
				imgEl.classList.add('d-none');
				imgEl.src = '';
				if (iconEl) {
					iconEl.classList.remove('d-none');
					iconEl.className = 'fas fa-robot text-dark fa-2x';
				}
			}
		} else {
			// Reset
			document.getElementById('displayToyMeta1').textContent = '';
			document.getElementById('displayToyMeta2').textContent = '';
			imgEl.classList.add('d-none');
			imgEl.src = '';
			if (iconEl) {
				iconEl.classList.remove('d-none');
				iconEl.className = 'fas fa-box-open text-muted fa-2x';
			}
		}
	};

	const renderWidgetResults = (filterText = '') => {
		if (!widgetList) return;
		widgetList.innerHTML = '';
		const term = filterText.toLowerCase();

		const filtered = currentMasterToysList.filter((t) =>
			t.name.toLowerCase().includes(term),
		);

		if (filtered.length === 0) {
			widgetList.innerHTML =
				'<div class="p-3 text-center text-muted small">No toys found matching "' +
				filterText +
				'"</div>';
			return;
		}

		filtered.forEach((toy) => {
			const div = document.createElement('div');
			div.className = 'toy-result-item';

			// Meta data liste
			let metaParts = [];
			if (toy.release_year) metaParts.push(toy.release_year);
			if (toy.type_name) metaParts.push(toy.type_name);
			if (toy.source_material_name) metaParts.push(toy.source_material_name);

			// Billede vs Ikon logik
			let imgHtml = '<i class="fas fa-robot text-muted"></i>';
			if (toy.image_path) {
				imgHtml = `<img src="${toy.image_path}" class="toy-thumb-img" alt="${toy.name}">`;
			}

			div.innerHTML = `
                <div class="toy-thumb-container">${imgHtml}</div>
                <div class="flex-grow-1">
                    <div class="toy-title">${toy.name}</div>
                    <div class="text-muted small">${metaParts.join(' &bull; ')}</div>
                </div>
            `;

			div.addEventListener('click', () => {
				widgetInput.value = toy.id;
				updateWidgetDisplay(toy);
				widgetOverlay.classList.remove('show');
				loadMasterToyItems(toy.id);
			});
			widgetList.appendChild(div);
		});
	};

	const resetSelect = (el, msg) => {
		if (el) {
			el.innerHTML = `<option value="">${msg}</option>`;
			el.disabled = true;
		}
	};

	const resetWidget = (msg) => {
		if (widgetCard) {
			widgetCard.classList.add('disabled');
			document.getElementById('displayToyTitle').textContent = msg;
			document.getElementById('displayToyMeta1').textContent = '';
			document.getElementById('displayToyMeta2').textContent = '';
			widgetInput.value = '';
			// Reset ikon
			const iconEl = document.getElementById('displayToyImgIcon');
			const imgEl = document.getElementById('displayToyImg');
			if (imgEl) {
				imgEl.classList.add('d-none');
				imgEl.src = '';
			}
			if (iconEl) {
				iconEl.classList.remove('d-none');
				iconEl.className = 'fas fa-box-open text-muted fa-2x';
			}
		}
	};

	const populateSelect = (el, data, defaultMsg) => {
		let options = `<option value="">${defaultMsg}</option>`;
		data.forEach((item) => {
			options += `<option value="${item.id}">${item.name}</option>`;
		});
		el.innerHTML = options;
		el.disabled = false;
	};

	// --- CORE LOGIC (Child Items) ---

	const refreshExistingRows = () => {
		const selects = container.querySelectorAll('.master-toy-item-select');
		selects.forEach((select) => {
			const currentVal = select.value;
			let options =
				availableMasterToyItems.length > 0
					? '<option value="">Select Item...</option>'
					: '<option value="">Unknown Items (Select Toy above first)</option>';
			if (availableMasterToyItems.length > 0) {
				availableMasterToyItems.forEach((mti) => {
					options += `<option value="${mti.id}">${mti.name} (${mti.type})</option>`;
				});
			}
			select.innerHTML = options;
			if (
				currentVal &&
				availableMasterToyItems.some((p) => p.id == currentVal)
			)
				select.value = currentVal;
		});
	};

	const addItemRow = async (data = null) => {
		// 1. Hent Master Toy items hvis de mangler
		if (availableMasterToyItems.length === 0 && widgetInput.value) {
			try {
				const res = await fetch(
					`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${widgetInput.value}`,
				);
				availableMasterToyItems = await res.json();
			} catch (e) {
				console.error(e);
			}
		}

		// 2. Klon Template og opdater ID'er
		const index = rowCount++;
		const clone = template.content.cloneNode(true);

		// Opdater navne og ID'er på inputs
		clone.querySelectorAll('[name*="INDEX"]').forEach((el) => {
			el.name = el.name.replace('INDEX', index);
			if (el.id) el.id = el.id.replace('INDEX', index);
		});

		// VIGTIGT: Opdater Collapse attributter (så "More Details" knappen virker unikt pr. række)
		clone.querySelectorAll('[data-bs-target*="INDEX"]').forEach((el) => {
			el.setAttribute(
				'data-bs-target',
				el.getAttribute('data-bs-target').replace('INDEX', index),
			);
			el.setAttribute(
				'aria-controls',
				el.getAttribute('aria-controls').replace('INDEX', index),
			);
		});
		clone.querySelectorAll('[id*="INDEX"]').forEach((el) => {
			el.id = el.id.replace('INDEX', index);
		});

		// Opdater labels (for attribut)
		clone.querySelectorAll('[for*="INDEX"]').forEach((el) => {
			el.setAttribute('for', el.getAttribute('for').replace('INDEX', index));
		});

		// 3. Setup Dropdown (Master Item)
		const masterToyItemSelect = clone.querySelector(
			'.master-toy-item-select',
		);
		const titleSpan = clone.querySelector('.item-display-name');
		const typeSpan = clone.querySelector('.item-type-display');

		if (availableMasterToyItems.length > 0) {
			let options = '<option value="">Select Item...</option>';
			availableMasterToyItems.forEach((mti) => {
				options += `<option value="${mti.id}">${mti.name} (${mti.type})</option>`;
			});
			masterToyItemSelect.innerHTML = options;
		} else {
			masterToyItemSelect.innerHTML =
				'<option value="">Unknown Items (Select Toy above first)</option>';
		}

		// Lytter til ændringer i dropdown for at opdatere titlen
		masterToyItemSelect.addEventListener('change', function () {
			const mti = availableMasterToyItems.find((p) => p.id == this.value);
			if (mti) {
				titleSpan.textContent = mti.name;
				if (typeSpan) typeSpan.textContent = ` (${mti.type})`;
			} else {
				titleSpan.textContent = 'New Item';
				if (typeSpan) typeSpan.textContent = '';
			}
		});

		// 4. Udfyld data (hvis det findes - fx ved edit eller "Add All")
		if (data) {
			const existingIdInput = clone.querySelector('.item-db-id');
			if (existingIdInput) existingIdInput.value = data.id;

			if (titleSpan)
				titleSpan.textContent = data.master_toy_item_name || 'Item';
			if (data.master_toy_item_type && typeSpan)
				typeSpan.textContent = ` (${data.master_toy_item_type})`;

			if (masterToyItemSelect) {
				masterToyItemSelect.value = data.master_toy_item_id;
				// Opdater visuel tekst med det samme
				masterToyItemSelect.dispatchEvent(new Event('change'));
			}

			// Hjælpefunktioner til sikkert at sætte værdier
			const setVal = (selector, val) => {
				const el = clone.querySelector(selector);
				if (el) el.value = val;
			};
			const setCheck = (selector, isChecked) => {
				const el = clone.querySelector(selector);
				if (el) el.checked = isChecked;
			};

			// Her bruger vi de CSS-klasser, der er i den nye template
			setCheck('.input-loose', data.is_loose == 1);
			setVal('.input-condition', data.condition || '');
			setVal('.input-repro', data.is_reproduction || '');

			// Felter i "More Details" sektionen
			setVal('.input-p-date', data.purchase_date || '');
			setVal('.input-price', data.purchase_price || '');
			setVal('.input-source', data.source_id || '');
			setVal('.input-acq', data.acquisition_status || '');
			setVal('.input-exp-date', data.expected_arrival_date || '');

			setVal('.input-pers-id', data.personal_item_id || '');
			setVal('.input-storage', data.storage_id || '');
			setVal('.input-comments', data.user_comments || '');
		}

		// 5. Slet-knap funktionalitet
		const deleteBtn = clone.querySelector('.remove-row-btn');
		if (deleteBtn) {
			deleteBtn.onclick = function (e) {
				e.preventDefault();
				const row = e.target.closest('.child-item-row');
				if (row) row.remove();
				if (countBadge)
					countBadge.textContent = `${container.querySelectorAll('.child-item-row').length} item(s)`;
			};
		}

		// 6. Indsæt i DOM
		container.appendChild(clone);

		// Opdater tæller
		if (countBadge)
			countBadge.textContent = `${container.querySelectorAll('.child-item-row').length} item(s)`;

		// Scroll ned (kun ved manuel tilføjelse)
		if (!data) {
			container.lastElementChild.scrollIntoView({
				behavior: 'smooth',
				block: 'center',
			});
		}
	};

	// Gør den tilgængelig globalt (VIGTIGT for at Add All knappen virker)
	if (window.CollectionForm) {
		window.CollectionForm.addItemRow = addItemRow;
	}

	// Init existing items
	if (container && container.dataset.items) {
		try {
			const existingItems = JSON.parse(container.dataset.items);
			if (Array.isArray(existingItems))
				existingItems.forEach((item) => addItemRow(item));
		} catch (e) {
			console.error('Error parsing items', e);
		}
	}

	// --- EVENTS & DROPDOWNS ---
	if (widgetCard) {
		widgetCard.addEventListener('click', function () {
			if (this.classList.contains('disabled')) return;
			if (widgetOverlay.classList.contains('show')) {
				widgetOverlay.classList.remove('show');
			} else {
				widgetOverlay.classList.add('show');
				widgetSearch.value = '';
				widgetSearch.focus();
				renderWidgetResults();
			}
		});
	}

	if (widgetSearch)
		widgetSearch.addEventListener('keyup', (e) =>
			renderWidgetResults(e.target.value),
		);

	document.addEventListener('click', function (event) {
		if (widgetOverlay && widgetOverlay.classList.contains('show')) {
			if (
				!widgetCard.contains(event.target) &&
				!widgetOverlay.contains(event.target)
			) {
				widgetOverlay.classList.remove('show');
			}
		}
	});

	const loadManufacturers = (universeId) => {
		if (!manufacturerSelect) return;
		manufacturerSelect.innerHTML = '<option>Loading...</option>';
		manufacturerSelect.disabled = true;
		resetSelect(lineSelect, 'Select Manufacturer first...');
		resetWidget('Select Line first...');
		if (!universeId) {
			resetSelect(manufacturerSelect, 'Select Universe first...');
			return;
		}

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_manufacturers&universe_id=${universeId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				populateSelect(manufacturerSelect, data, 'Select Manufacturer...');
				if (data.length === 1) {
					manufacturerSelect.value = data[0].id;
					manufacturerSelect.dispatchEvent(new Event('change'));
				}
			});
	};

	const loadLines = (manId) => {
		lineSelect.innerHTML = '<option>Loading...</option>';
		lineSelect.disabled = true;
		resetWidget('Select Line first...');
		if (!manId) return;
		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_lines&manufacturer_id=${manId}&universe_id=${universeSelect.value}`,
		)
			.then((res) => res.json())
			.then((data) => {
				populateSelect(lineSelect, data, 'Select Line...');
				if (data.length === 1) {
					lineSelect.value = data[0].id;
					lineSelect.dispatchEvent(new Event('change'));
				}
			});
	};

	const loadToys = (lineId) => {
		widgetInput.value = '';
		currentMasterToysList = [];
		if (widgetCard) widgetCard.classList.add('disabled');
		document.getElementById('displayToyTitle').textContent = 'Loading...';
		if (!lineId) {
			document.getElementById('displayToyTitle').textContent =
				'Select Line first...';
			return;
		}

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toys&line_id=${lineId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				currentMasterToysList = data;
				if (widgetCard) widgetCard.classList.remove('disabled');
				document.getElementById('displayToyTitle').textContent =
					'Select Toy / Set...';
			});
	};

	const loadMasterToyItems = (toyId) => {
		availableMasterToyItems = [];

		// Reset container data attribut
		const container = document.getElementById('childItemsContainer');
		if (container) container.dataset.masterToyItems = '[]';

		// Knap referencer
		const colAll = document.getElementById('colAddAll');
		const colSingle = document.getElementById('colAddSingle');

		if (!toyId) {
			refreshExistingRows();
			// Skjul 'Add All' knap
			if (colAll) colAll.classList.add('d-none');
			if (colSingle) {
				colSingle.classList.remove('col-6');
				colSingle.classList.add('col-12');
			}
			return;
		}

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${toyId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				availableMasterToyItems = data;

				// Opdater data på containeren
				if (container)
					container.dataset.masterToyItems = JSON.stringify(data);

				refreshExistingRows();

				// --- Opdater Add All knappen ---
				if (colAll && colSingle) {
					if (data.length > 0) {
						colAll.classList.remove('d-none');
						colAll.classList.add('col-6');
						colSingle.classList.remove('col-12');
						colSingle.classList.add('col-6');
					} else {
						colAll.classList.add('d-none');
						colAll.classList.remove('col-6');
						colSingle.classList.remove('col-6');
						colSingle.classList.add('col-12');
					}
				}
			})
			.catch((err) => console.error('Error loading items:', err));
	};

	if (universeSelect) {
		universeSelect.addEventListener('change', (e) =>
			loadManufacturers(e.target.value),
		);
		if (universeSelect.value && manufacturerSelect.options.length <= 1)
			loadManufacturers(universeSelect.value);
	}
	if (manufacturerSelect)
		manufacturerSelect.addEventListener('change', (e) =>
			loadLines(e.target.value),
		);
	if (lineSelect)
		lineSelect.addEventListener('change', (e) => loadToys(e.target.value));
	if (btnAddItem) btnAddItem.addEventListener('click', () => addItemRow());

	// --- FIX: INITIALISER WIDGET I EDIT MODE ---
	if (widgetWrapper) {
		if (widgetWrapper.dataset.allToys) {
			try {
				currentMasterToysList = JSON.parse(widgetWrapper.dataset.allToys);
			} catch (e) {
				console.error('Error parsing all toys', e);
			}
		}
		if (widgetWrapper.dataset.selectedToy) {
			try {
				const selectedToy = JSON.parse(widgetWrapper.dataset.selectedToy);
				if (selectedToy) {
					updateWidgetDisplay(selectedToy);
					if (widgetCard) widgetCard.classList.remove('disabled');
				}
			} catch (e) {
				console.error('Error parsing selected toy', e);
			}
		}
	}

	// --- AJAX SUBMIT HANDLER ---
	const form = document.getElementById('addToyForm');
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();

			const container = document.getElementById('childItemsContainer');
			if (
				container &&
				container.querySelectorAll('.child-item-row').length === 0
			) {
				alert('You must add at least one Item before saving.');
				const btnAddItem = document.getElementById('btnAddItemRow');
				if (btnAddItem)
					btnAddItem.scrollIntoView({
						behavior: 'smooth',
						block: 'center',
					});
				return;
			}

			const formData = new FormData(form);
			const submitBtn = form.querySelector('button[type="submit"]');
			const originalBtnText = submitBtn.innerHTML;

			submitBtn.innerHTML =
				'<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
			submitBtn.disabled = true;

			fetch(form.action, { method: 'POST', body: formData })
				.then((response) => {
					const contentType = response.headers.get('content-type');
					if (
						contentType &&
						contentType.indexOf('application/json') !== -1
					) {
						return response.json().then((data) => {
							submitBtn.disabled = false;
							submitBtn.innerHTML = originalBtnText;

							if (data.success) {
								if (
									window.CollectionForm &&
									typeof window.CollectionForm.handleSaveSuccess ===
										'function'
								) {
									window.CollectionForm.handleSaveSuccess(
										data,
										'addToyForm',
									);
								} else {
									window.location.reload();
								}
							} else {
								alert('Error: ' + data.error);
							}
						});
					} else {
						return response.text().then((html) => {
							const modalContent = form.closest('.modal-content');
							if (modalContent) {
								modalContent.innerHTML = html;
								if (typeof App.initMediaUploads === 'function')
									App.initMediaUploads();
							}
						});
					}
				})
				.catch((err) => {
					console.error('Save error:', err);
					alert('Error saving.');
					submitBtn.innerHTML = originalBtnText;
					submitBtn.disabled = false;
				});
		});
	}

	// --- NYT: Tjek for Auto-Fill Trigger ---
	const autoAddTrigger = document.getElementById('triggerAutoAddItems');
	if (autoAddTrigger && window.CollectionForm) {
		// Vi venter et kort øjeblik for at sikre at DOM og data er klar
		setTimeout(() => {
			console.log('Auto-Add Trigger detected: Adding all items...');
			window.CollectionForm.addAllItemsFromMaster();
		}, 100);
	}
};

window.CollectionForm = {
	// Åbner modal til at oprette nyt legetøj
	openAddModal: function () {
		App.openModal('Collection', 'Toy', 'add');
	},

	// Åbner modal til redigering af data
	openEditModal: function (id) {
		if (!id) return console.error('Missing ID for edit modal');
		App.openModal('Collection', 'Toy', 'edit', { id: id });
	},

	// Åbner modal til billeder (Step 3 / Media Step)
	openMediaModal: function (id) {
		if (!id) return console.error('Missing ID for media modal');
		App.openModal('Collection', 'Toy', 'media_step', { id: id });
	},

	// Callback når noget gemmes
	handleSaveSuccess: function (data, formId) {
		const modalEl = document.getElementById('appModal');

		// 1. Find ID
		let refreshId = null;
		if (data && data.id) {
			refreshId = data.id;
		} else if (formId) {
			const form = document.getElementById(formId);
			const idInput = form ? form.querySelector('input[name="id"]') : null;
			refreshId = idInput ? idInput.value : null;
		}

		// 2. Luk modalen
		if (modalEl) {
			if (document.activeElement) {
				document.activeElement.blur();
			}

			const modal = bootstrap.Modal.getInstance(modalEl);
			if (modal) modal.hide();

			const closeBtn = modalEl.querySelector(
				'.btn-close, [data-bs-dismiss="modal"]',
			);
			if (closeBtn) closeBtn.click();

			setTimeout(() => {
				if (modalEl.classList.contains('show')) {
					modalEl.classList.remove('show');
					modalEl.style.display = 'none';
					document.body.classList.remove('modal-open');
					const backdrops = document.querySelectorAll('.modal-backdrop');
					backdrops.forEach((bd) => bd.remove());
				}
			}, 100);
		}

		App.showToast('Saved successfully!');

		// 3. UNIVERSEL SMART REFRESH
		// Tjek om Manageren er indlæst
		const mgrAvailable = typeof CollectionMgr !== 'undefined';

		// Tjek om kortet rent faktisk findes i DOM'en på den nuværende side
		let cardOnPage = null;
		if (mgrAvailable && CollectionMgr.container) {
			cardOnPage = CollectionMgr.container.querySelector(
				`[data-id="${refreshId}"]`,
			);
		}
		if (!cardOnPage) {
			cardOnPage = document.querySelector(
				`.toy-card[data-id="${refreshId}"], tr[data-id="${refreshId}"]`,
			);
		}

		if (data && data.success && mgrAvailable && cardOnPage) {
			// YES: Kortet er her -> Opdater det (Gælder både Collection List og Dashboard)
			console.log('Smart Refresh: Updating item ' + refreshId);
			CollectionMgr.refreshItem(refreshId);
		} else {
			// NO: Kortet mangler (f.eks. ny oprettelse) eller fejl -> Reload siden
			console.log('Smart Refresh: Item not found or new -> Reloading page');
			setTimeout(() => window.location.reload(), 300);
		}
	},

	/**
	 * NY FUNKTION: Tilføjer alle definerede dele fra Master Toy som rækker
	 */
	addAllItemsFromMaster: function () {
		const container = document.getElementById('childItemsContainer');
		if (!container) return;

		// 1. Hent master definitions
		let masterItems = [];
		try {
			masterItems = JSON.parse(container.dataset.masterToyItems || '[]');
		} catch (e) {
			console.error('Could not parse master toy items', e);
			return;
		}

		if (masterItems.length === 0) {
			alert('No parts/items defined for this Master Toy in the catalog.');
			return;
		}

		// 2. Bekræft hvis der er mange dele (valgfrit, men god UX)
		if (
			masterItems.length > 10 &&
			!confirm(`Add all ${masterItems.length} items?`)
		) {
			return;
		}

		// 3. Loop og tilføj
		masterItems.forEach((masterItem) => {
			// Vi konstruerer et data-objekt, som addItemRow forstår
			const itemData = {
				master_toy_item_id: masterItem.id, // ID'et på delen
				condition: '', // Standard værdi
				is_loose: 1, // Standard (ofte løs hvis man tilføjer dele)
				quantity: 1,
			};

			this.addItemRow(itemData);
		});

		// Scroll til bunden af listen så man ser de nye items
		const lastRow = container.lastElementChild;
		if (lastRow) lastRow.scrollIntoView({ behavior: 'smooth', block: 'end' });
	},
};
