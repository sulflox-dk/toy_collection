/**
 * Collection Module Logic
 */

// --- DEL 1: FORM LOGIK (Trin 2 - Add/Edit Toy & Items) ---
App.initDependentDropdowns = function () {
	console.log('Initializing Toy Form logic...');

	const universeSelect = document.getElementById('selectUniverse');
	const manufacturerSelect = document.getElementById('selectManufacturer');
	const lineSelect = document.getElementById('selectLine');
    
    // NYT: Widget elementer i stedet for selectMasterToy
    const widgetInput = document.getElementById('inputMasterToyId');
    const widgetCard = document.getElementById('masterToyDisplayCard');
    const widgetOverlay = document.getElementById('masterToyOverlay');
    const widgetSearch = document.getElementById('inputToySearch');
    const widgetList = document.getElementById('toyResultsList');

	const btnAddItem = document.getElementById('btnAddItemRow');
	const container = document.getElementById('childItemsContainer');
	const template = document.getElementById('childRowTemplate');
	const countBadge = document.getElementById('itemCountBadge');

	let availableMasterToyItems = []; // Var før "parts"
    let currentMasterToysList = [];   // NYT: Liste til søge-widgetten
	let rowCount = 0;

	// Hent data fra containeren (Edit mode data og items)
	if (container) {
		try {
			if (container.dataset.masterToyItems) {
				availableMasterToyItems = JSON.parse(container.dataset.masterToyItems);
			}
		} catch (e) {
			console.error('JSON parse error in master items', e);
		}
	}

	// --- HELPERS ---
    
    // Opdaterer display kortet med det valgte toy (Widget Logik)
    const updateWidgetDisplay = (toy) => {
        const iconEl = document.getElementById('displayToyImgIcon');
        const imgEl = document.getElementById('displayToyImg');

        document.getElementById('displayToyTitle').textContent = toy ? toy.name : 'Select Toy...';
        
        if (toy) {
            // Linie 2: Year + Type
            const line2 = [toy.release_year, toy.type_name].filter(Boolean).join(' - ');
            document.getElementById('displayToyMeta1').textContent = line2;
            
            // Linie 3: Source Material (Filmen/Serien) - Fallback til Wave nummer
            // NYT HER: Vi bruger source_material_name fra databasen
            const sourceText = toy.source_material_name || (toy.wave_number ? `Wave: ${toy.wave_number}` : '');
            document.getElementById('displayToyMeta2').textContent = sourceText;

            // Ikon styring
            if(iconEl) iconEl.className = 'fas fa-robot text-dark fa-2x';
        } else {
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            if(iconEl) iconEl.className = 'fas fa-box-open text-muted fa-2x';
        }
    };

    // Genererer HTML listen i overlayet baseret på søgning
    const renderWidgetResults = (filterText = '') => {
        if(!widgetList) return;
        widgetList.innerHTML = '';
        const term = filterText.toLowerCase();
        
        // Filtrer listen
        const filtered = currentMasterToysList.filter(t => t.name.toLowerCase().includes(term));

        if (filtered.length === 0) {
            widgetList.innerHTML = '<div class="p-3 text-center text-muted small">No toys found matching "' + filterText + '"</div>';
            return;
        }

        filtered.forEach(toy => {
            const div = document.createElement('div');
            div.className = 'toy-result-item';
            div.innerHTML = `
                <div class="toy-thumb-container">
                    <i class="fas fa-robot text-muted"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="toy-title">${toy.name}</div>
                    <div class="text-muted small">
                        ${[toy.release_year, toy.type_name].filter(Boolean).join(' ? ')}
                    </div>
                </div>
            `;
            
            // CLICK EVENT: Vælg toy fra listen
            div.addEventListener('click', () => {
                widgetInput.value = toy.id;
                updateWidgetDisplay(toy);           // Opdater kortet
                widgetOverlay.classList.remove('show'); // Luk overlay
                loadMasterToyItems(toy.id);         // Hent items til bunden (den gamle 'change' event)
            });
            
            widgetList.appendChild(div);
        });
    };

    // Reset funktion til dropdowns
	const resetSelect = (el, msg) => {
		if (el) {
			el.innerHTML = `<option value="">${msg}</option>`;
			el.disabled = true;
		}
	};
    
    // Helper til at nulstille widgetten når Man/Line skifter
    const resetWidget = (msg) => {
        if(widgetCard) {
            widgetCard.classList.add('disabled');
            document.getElementById('displayToyTitle').textContent = msg;
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            widgetInput.value = '';
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

	const updateCount = () => {
		const count = container.querySelectorAll('.child-item-row').length;
		if (countBadge) countBadge.textContent = `${count} items`;
	};

	// --- CORE ITEM LOGIC ---
	
	// Opdater dropdowns i ALLE eksisterende rækker
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
			// Prøv at bevare værdien hvis den stadig findes
			if (currentVal && availableMasterToyItems.some((p) => p.id == currentVal)) {
				select.value = currentVal;
			}
		});
	};

	// Den "kloge" add funktion der håndterer både nye og eksisterende
	const addItemRow = async (data = null) => {
		// Hvis vi ikke har hentet master items endnu, men har valgt en toy, gør det nu
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

		const index = rowCount++;
		const clone = template.content.cloneNode(true);
		const row = clone.querySelector('.child-item-row');

		// 1. Unikke navne/IDs
		clone.querySelectorAll('[name*="INDEX"]').forEach((el) => {
			el.name = el.name.replace('INDEX', index);
			if (el.id) el.id = el.id.replace('INDEX', index);
		});
		clone.querySelectorAll('[for*="INDEX"]').forEach((el) => {
			el.setAttribute('for', el.getAttribute('for').replace('INDEX', index));
		});

		// 2. Setup Dropdown & LIVE Update logik
		const masterToyItemSelect = clone.querySelector('.master-toy-item-select');
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

		// LYTTER: Opdater headeren når man vælger i dropdownen
		masterToyItemSelect.addEventListener('change', function () {
			const selectedId = this.value;
			const mti = availableMasterToyItems.find((p) => p.id == selectedId);
			if (mti) {
				titleSpan.textContent = mti.name;
				if (typeSpan) typeSpan.textContent = ` (${mti.type})`;
			} else {
				titleSpan.textContent = 'New Item';
				if (typeSpan) typeSpan.textContent = '';
			}
		});

		// 3. Håndter Data (Edit Mode)
		if (data) {
			// Gem ID til sletning
			const idInput = document.createElement('input');
			idInput.type = 'hidden';
			idInput.name = `items[${index}][id]`;
			idInput.value = data.id;
			idInput.className = 'item-db-id'; 
			row.prepend(idInput);

			// Udfyld overskrift og type fra databasen
			titleSpan.textContent = data.master_toy_item_name || 'Item';
			if (data.master_toy_item_type && typeSpan) {
				typeSpan.textContent = ` (${data.master_toy_item_type})`;
			}

			if (masterToyItemSelect) masterToyItemSelect.value = data.master_toy_item_id;
			if (data.is_loose == 1)
				clone.querySelector('.input-loose').checked = true;
			else clone.querySelector('.input-loose').checked = false;

			clone.querySelector('.input-condition').value = data.condition || '';
			clone.querySelector('.input-repro').value = data.is_reproduction || '';
			clone.querySelector('[name*="[purchase_date]"]').value = data.purchase_date || '';
			clone.querySelector('[name*="[purchase_price]"]').value = data.purchase_price || '';
			clone.querySelector('[name*="[source_id]"]').value = data.source_id || '';
			clone.querySelector('[name*="[acquisition_status]"]').value = data.acquisition_status || '';
			clone.querySelector('[name*="[expected_arrival_date]"]').value = data.expected_arrival_date || '';
			clone.querySelector('[name*="[personal_item_id]"]').value = data.personal_item_id || '';
			clone.querySelector('[name*="[storage_id]"]').value = data.storage_id || '';
			clone.querySelector('[name*="[user_comments]"]').value = data.user_comments || '';

			// SLET LOGIK (Eksisterende items)
			const deleteBtn = clone.querySelector('.remove-row-btn');
			deleteBtn.onclick = function (e) {
				e.preventDefault();
				if (data.id) {
					App.deleteToyItem(data.id, this);
				}
			};
		} else {
			// SLET LOGIK (Nye items - fjern kun fra HTML)
			const deleteBtn = clone.querySelector('.remove-row-btn');
			deleteBtn.onclick = function (e) {
				e.preventDefault();
				e.target.closest('.child-item-row').remove();
				updateCount();
			};
		}

		container.appendChild(clone);
		updateCount();

		// Scroll til bunden kun hvis det er manuel tilføjelse
		if (!data) {
			const newRow = container.lastElementChild;
			if (newRow)
				newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	};

	// --- INIT EXISTING ITEMS ---
	if (container && container.dataset.items) {
		try {
			const existingItems = JSON.parse(container.dataset.items);
			if (Array.isArray(existingItems)) {
				existingItems.forEach((item) => addItemRow(item));
			}
		} catch (e) {
			console.error('Error parsing items', e);
		}
	}

    // --- WIDGET EVENT LISTENERS ---
    if(widgetCard) {
		widgetCard.addEventListener('click', function() {
			if (this.classList.contains('disabled')) return;
			
			// Toggle overlay
			const isMsgOpen = widgetOverlay.classList.contains('show');
			if (isMsgOpen) {
				widgetOverlay.classList.remove('show');
			} else {
				widgetOverlay.classList.add('show');
				widgetSearch.value = ''; // Reset søgning
				widgetSearch.focus();
				renderWidgetResults(); // Vis alle
			}
		});
	}

	if(widgetSearch) {
		widgetSearch.addEventListener('keyup', (e) => {
			renderWidgetResults(e.target.value);
		});
	}

	// Luk overlay hvis man klikker udenfor
	document.addEventListener('click', function(event) {
		if (widgetOverlay && widgetOverlay.classList.contains('show')) {
			if (!widgetCard.contains(event.target) && !widgetOverlay.contains(event.target)) {
				widgetOverlay.classList.remove('show');
			}
		}
	});

	// --- DROPDOWNS (Universe/Line/Etc) ---
	
	// Hjælpefunktion til auto-valg
	const autoSelectIfSingle = (element, data) => {
		if (data.length === 1) {
			element.value = data[0].id;
			element.dispatchEvent(new Event('change'));
		}
	};

	const loadManufacturers = (universeId) => {
		if (!manufacturerSelect) return;
		manufacturerSelect.innerHTML = '<option>Loading...</option>';
		manufacturerSelect.disabled = true;
		resetSelect(lineSelect, 'Select Manufacturer first...');
        
        // RETTET: Reset widget i stedet for dropdown
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
				autoSelectIfSingle(manufacturerSelect, data);
			});
	};

	const loadLines = (manId) => {
		lineSelect.innerHTML = '<option>Loading...</option>';
		lineSelect.disabled = true;
        // RETTET: Reset widget i stedet for dropdown
		resetWidget('Select Line first...');

		const uniId = universeSelect.value;
		if (!manId) return;

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_lines&manufacturer_id=${manId}&universe_id=${uniId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				populateSelect(lineSelect, data, 'Select Line...');
				autoSelectIfSingle(lineSelect, data);
			});
	};

    // RETTET: Den nye loadToys funktion der aktiverer widgetten
	const loadToys = (lineId) => {
		// Reset widget state
		widgetInput.value = '';
        currentMasterToysList = [];
		if (widgetCard) widgetCard.classList.add('disabled');
        document.getElementById('displayToyTitle').textContent = 'Loading...';
		
		if (!lineId) {
            document.getElementById('displayToyTitle').textContent = 'Select Line first...';
			return;
		}

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toys&line_id=${lineId}`,
		)
			.then((res) => res.json())
			.then((data) => {
                // Gem data til søgning
                currentMasterToysList = data;
                
                // Aktiver kortet
                if (widgetCard) widgetCard.classList.remove('disabled');
				document.getElementById('displayToyTitle').textContent = 'Select Toy / Set...';
			});
	};

	const loadMasterToyItems = (toyId) => {
		availableMasterToyItems = [];
		if (!toyId) {
			refreshExistingRows();
			return;
		}
		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${toyId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				availableMasterToyItems = data;
				refreshExistingRows();
			});
	};

	// Event listeners
	if (universeSelect) {
		universeSelect.addEventListener('change', (e) =>
			loadManufacturers(e.target.value),
		);
		// Edit mode fix
		if (
			universeSelect.value &&
			manufacturerSelect &&
			manufacturerSelect.options.length <= 1
		) {
			loadManufacturers(universeSelect.value);
		}
	}
	if (manufacturerSelect)
		manufacturerSelect.addEventListener('change', (e) =>
			loadLines(e.target.value),
		);
	if (lineSelect)
		lineSelect.addEventListener('change', (e) => loadToys(e.target.value));
    
    // RETTET: Vi har fjernet toySelect listeneren, da loadMasterToyItems nu kaldes når man klikker i widgetten

	if (btnAddItem) btnAddItem.addEventListener('click', () => addItemRow());

	// Ajax submit (denne del er uændret fra før)
	const form = document.getElementById('addToyForm');
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			const rowCount = container.querySelectorAll('.child-item-row').length;
			if (rowCount === 0) {
				alert(
					'You must add at least one Item (Figure/Part) before saving.',
				);
				btnAddItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
					const contentType = response.headers.get("content-type");
					if (contentType && contentType.indexOf("application/json") !== -1) {
						return response.json().then(data => {
							if (data.success) {
								window.location.reload();
							} else {
								alert('Error saving: ' + (data.error || 'Unknown error'));
								submitBtn.disabled = false;
								submitBtn.innerHTML = originalBtnText;
							}
						});
					} else {
						return response.text().then(html => {
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
					alert('An error occurred while saving.');
					submitBtn.innerHTML = originalBtnText;
					submitBtn.disabled = false;
				});
		});
	}
};

// --- DEL 2: MEDIA UPLOAD LOGIK (Behold denne som den er) ---
App.initMediaUploads = function () {
	// ... (Resten af koden her er uændret og kan beholdes, eller kopieret fra din tidligere version) ...
    // FOR FULDSTÆNDIGHEDENS SKYLD HAR JEG KOPIERET DET HELE MED HERUNDER:
    console.log('Initializing Media Uploader...');

	const containerEl = document.getElementById('media-upload-container');

	const buildTagPills = (activeTags) => {
		if (!window.availableMediaTags) return '';
		return window.availableMediaTags
			.map((tag) => {
				const isActive =
					activeTags && activeTags.some((t) => t.id == tag.id);
				const bgClass = isActive
					? 'bg-dark text-white'
					: 'bg-light text-dark';
				return `
                <span class="badge rounded-pill ${bgClass} border tag-pill" data-id="${tag.id}">
                    ${tag.tag_name}
                </span>`;
			})
			.join('');
	};

	const saveMetadata = (mediaId, rowElement) => {
		const commentInput = rowElement.querySelector('.media-comment-input');
		const mainInput = rowElement.querySelector('.media-main-input');
		const indicator = rowElement.querySelector('.save-indicator');
		const activePills = rowElement.querySelectorAll('.tag-pill.bg-dark');
		const selectedTags = Array.from(activePills).map(
			(pill) => pill.dataset.id,
		);

		const formData = new FormData();
		formData.append('media_id', mediaId);
		formData.append('user_comment', commentInput.value);
		formData.append('is_main', mainInput.checked ? 1 : 0);
		selectedTags.forEach((tag) => formData.append('tags[]', tag));

		fetch(
			`${App.baseUrl}?module=Media&controller=Media&action=update_metadata`,
			{
				method: 'POST',
				body: formData,
			},
		)
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					indicator.classList.remove('opacity-0');
					setTimeout(() => indicator.classList.add('opacity-0'), 2000);
				}
			})
			.catch((err) => console.error('Save failed', err));
	};

	const createMediaRow = (container, data) => {
		const rowDiv = document.createElement('div');
		rowDiv.className = 'media-preview-row fade-in-row';

		const isMainChecked = data.is_main == 1 ? 'checked' : '';

		rowDiv.innerHTML = `
            <div class="media-img-container">
                <div class="media-img-frame">
                    <img src="${data.file_path}" alt="Toy Photo">
                </div>
                
                <div class="form-check form-check-sm user-select-none mt-2">
                    <input class="form-check-input media-main-input" type="radio" 
                           name="main_image_${data.media_id}" id="main_${data.media_id}" ${isMainChecked}>
                    <label class="form-check-label small text-muted" for="main_${data.media_id}" style="cursor: pointer;">Main Image</label>
                </div>

				<button type="button" 
						class="btn btn-sm btn-outline-secondary px-2 delete-btn-general delete-photo-btn" 
						onclick="App.deleteMedia(${data.media_id}, this)">
					<i class="far fa-trash-alt me-1"></i> Delete
				</button>
            </div>

            <div class="flex-grow-1 d-flex flex-column">
                <span class="badge bg-success opacity-0 save-indicator">
                    <i class="fas fa-check me-1"></i>Saved
                </span>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label small text-muted mb-2">Tags</label>
                        <div class="d-flex flex-wrap gap-2 tag-container">
                            ${buildTagPills(data.tags)}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-2">Comments</label>
                        <textarea class="form-control media-comment-input" rows="2" 
                                  placeholder="Describe photo...">${data.user_comment || ''}</textarea>
                    </div>
                </div>
            </div>
        `;

		container.appendChild(rowDiv);

		// Listeners for the new row
		rowDiv.querySelectorAll('.tag-pill').forEach((pill) => {
			pill.addEventListener('click', function () {
				if (this.classList.contains('bg-dark')) {
					this.classList.replace('bg-dark', 'bg-light');
					this.classList.replace('text-white', 'text-dark');
				} else {
					this.classList.replace('bg-light', 'bg-dark');
					this.classList.replace('text-dark', 'text-white');
				}
				saveMetadata(data.media_id, rowDiv);
			});
		});

		rowDiv
			.querySelector('.media-comment-input')
			.addEventListener('change', () => saveMetadata(data.media_id, rowDiv));

		rowDiv
			.querySelector('.media-main-input')
			.addEventListener('change', function () {
				if (this.checked) {
					container.querySelectorAll('.media-main-input').forEach((cb) => {
						if (cb !== this) cb.checked = false;
					});
					saveMetadata(data.media_id, rowDiv);
				}
			});
	};

	// 2. INITIALIZE
	if (containerEl && containerEl.dataset.tags) {
		try {
			window.availableMediaTags = JSON.parse(containerEl.dataset.tags);
		} catch (e) {
			console.error('Failed to parse tags', e);
		}
	}

	if (containerEl && containerEl.dataset.existingMedia) {
		try {
			const mediaData = JSON.parse(containerEl.dataset.existingMedia);
			if (mediaData.parent && mediaData.parent.length > 0) {
				const pInput = containerEl.querySelector(
					'.upload-input[data-context="collection_parent"]',
				);
				const pContainer = document.getElementById(
					`preview-parent-${pInput.dataset.id}`,
				);
				if (pContainer)
					mediaData.parent.forEach((img) =>
						createMediaRow(pContainer, img),
					);
			}
			if (mediaData.items) {
				mediaData.items.forEach((item) => {
					const cContainer = document.getElementById(
						`preview-child-${item.id}`,
					);
					if (cContainer && item.images)
						item.images.forEach((img) => createMediaRow(cContainer, img));
				});
			}
		} catch (e) {
			console.error('Failed to parse existing media', e);
		}
	}

	document.querySelectorAll('.upload-input').forEach((input) => {
		input.addEventListener('change', function () {
			if (this.files)
				Array.from(this.files).forEach((file) =>
					handleUpload(file, this.dataset.context, this.dataset.id, this),
				);
		});
	});

	const handleUpload = (file, context, id, inputElement) => {
		const formData = new FormData();
		formData.append('file', file);
		formData.append('target_context', context);
		formData.append('target_id', id);

		const labelBtn = inputElement.parentElement;
		const icon = labelBtn.querySelector('i');
		if (icon) icon.className = 'fas fa-spinner fa-spin me-1';
		labelBtn.classList.add('disabled');

		fetch(`${App.baseUrl}?module=Media&controller=Media&action=upload`, {
			method: 'POST',
			body: formData,
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					const viewType =
						context === 'collection_parent' ? 'parent' : 'child';
					const container = document.getElementById(
						`preview-${viewType}-${id}`,
					);
					if (container) createMediaRow(container, data);
				}
			})
			.finally(() => {
				if (icon) icon.className = 'fas fa-plus me-1';
				labelBtn.classList.remove('disabled');
				inputElement.value = '';
			});
	};
};

App.deleteMedia = function (mediaId, btnElement) {
	if (!confirm('Are you sure you want to delete this photo?')) return;

	fetch(
		`${App.baseUrl}?module=Collection&controller=Api&action=delete_media&id=${mediaId}`,
		{ method: 'POST' },
	)
		.then((res) => res.json())
		.then((data) => {
			if (data.success) {
				// Fjern hele rækken med en lille animation
				const row = btnElement.closest('.media-preview-row');
				row.style.opacity = '0';
				setTimeout(() => row.remove(), 300);
			} else {
				alert('Error deleting photo: ' + (data.error || 'Unknown error'));
			}
		})
		.catch((err) => console.error('Delete request failed', err));
};

// GLOBAL: Delete Item Logic
App.deleteToyItem = function (itemId, btnElement) {
	if (
		!confirm(
			'Are you sure you want to remove this item from your collection?',
		)
	)
		return;

	fetch(
		`${App.baseUrl}?module=Collection&controller=Api&action=delete_item&id=${itemId}`,
		{
			method: 'POST',
		},
	)
		.then((res) => res.json())
		.then((data) => {
			if (data.success) {
				// Fjern kortet med en animation
				const row = btnElement.closest('.child-item-row');
				row.style.opacity = '0';
				setTimeout(() => {
					row.remove();
					// Opdater badge tælleren hvis den findes
					const badge = document.getElementById('itemCountBadge');
					if (badge) {
						const currentCount =
							document.querySelectorAll('.child-item-row').length;
						badge.textContent = `${currentCount} items`;
					}
				}, 300);
			} else {
				alert('Error: ' + (data.error || 'Unknown error'));
			}
		});
};