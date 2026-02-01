/**
 * Collection Module Logic
 */

// --- DEL 1: FORM LOGIK (Trin 2 - Add/Edit Toy & Items) ---
App.initDependentDropdowns = function () {
	console.log('Initializing Toy Form logic...');

	const universeSelect = document.getElementById('selectUniverse');
	const manufacturerSelect = document.getElementById('selectManufacturer');
	const lineSelect = document.getElementById('selectLine');
	const toySelect = document.getElementById('selectMasterToy');

	const btnAddItem = document.getElementById('btnAddItemRow');
	const container = document.getElementById('childItemsContainer');
	const template = document.getElementById('childRowTemplate');
	const countBadge = document.getElementById('itemCountBadge');

	let availableParts = [];
	let rowCount = 0;

	// Hent data fra containeren (Edit mode data og dele)
	if (container) {
		try {
			if (container.dataset.parts) {
				availableParts = JSON.parse(container.dataset.parts);
			}
		} catch (e) {
			console.error('JSON parse error in parts', e);
		}
	}

	// --- HELPERS ---
	const resetSelect = (el, msg) => {
		if (el) {
			el.innerHTML = `<option value="">${msg}</option>`;
			el.disabled = true;
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
	const refreshExistingRows = () => {
		// Opdater dropdowns i ALLE rækker hvis parent toy skifter
		const selects = container.querySelectorAll('.item-part-select');
		selects.forEach((select) => {
			const currentVal = select.value;
			let options =
				availableParts.length > 0
					? '<option value="">Select Item...</option>'
					: '<option value="">Unknown Parts (Select Toy above first)</option>';

			if (availableParts.length > 0) {
				availableParts.forEach((part) => {
					options += `<option value="${part.id}">${part.name} (${part.type})</option>`;
				});
			}
			select.innerHTML = options;
			// Prøv at bevare værdien hvis den stadig findes
			if (currentVal && availableParts.some((p) => p.id == currentVal)) {
				select.value = currentVal;
			}
		});
	};

	// Den "kloge" add funktion der håndterer både nye og eksisterende
	const addItemRow = (data = null) => {
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

		// 2. Setup Dropdown
		const partSelect = row.querySelector('.item-part-select');
		let options =
			availableParts.length > 0
				? '<option value="">Select Item...</option>'
				: '<option value="">Select Master Toy first...</option>';

		if (availableParts.length > 0) {
			availableParts.forEach((part) => {
				options += `<option value="${part.id}">${part.name} (${part.type})</option>`;
			});
		}
		partSelect.innerHTML = options;

		// 3. Håndter Data (Edit Mode) vs Ny
		const deleteBtn = row.querySelector('.remove-row-btn');
		const titleSpan = row.querySelector('.item-display-name');

		if (data) {
			// EDIT MODE: Udfyld felter
			row.querySelector('.item-db-id').value = data.id;
			titleSpan.textContent = data.part_name || 'Item';

			if (partSelect) partSelect.value = data.master_toy_item_id;
			if (data.is_loose == 1) row.querySelector('.input-loose').checked = true;
			else row.querySelector('.input-loose').checked = false;

			row.querySelector('.input-condition').value = data.condition || '';
			row.querySelector('.input-repro').value = data.is_reproduction || '';
			row.querySelector('.input-p-date').value = data.purchase_date || '';
			row.querySelector('.input-price').value = data.purchase_price || '';
			row.querySelector('.input-source').value = data.source_id || '';
			row.querySelector('.input-acq').value = data.acquisition_status || '';
			row.querySelector('.input-exp-date').value =
				data.expected_arrival_date || '';
			row.querySelector('.input-pers-id').value = data.personal_item_id || '';
			row.querySelector('.input-storage').value = data.storage_id || '';
			row.querySelector('.input-comments').value = data.user_comments || '';

			// SLET LOGIK FOR DATABASE ITEMS
			deleteBtn.setAttribute('title', 'Remove Item from Collection');
			deleteBtn.onclick = function (e) {
				e.preventDefault(); // Undgå form submit
				if (data.id) {
					App.deleteToyItem(data.id, this);
				}
			};
		} else {
			// NY ITEM
			// SLET LOGIK FOR NYE (DOM fjernelse)
			deleteBtn.onclick = function (e) {
				e.preventDefault();
				row.remove();
				updateCount();
			};
		}

		container.appendChild(clone);
		updateCount();

		// Scroll til bunden hvis det er en manuel tilføjelse (ikke data load)
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

	// --- DROPDOWNS (Universe/Line/Etc) ---
	const loadManufacturers = (universeId) => {
		if (!manufacturerSelect) return;
		manufacturerSelect.innerHTML = '<option>Loading...</option>';
		manufacturerSelect.disabled = true;
		resetSelect(lineSelect, 'Select Manufacturer first...');
		resetSelect(toySelect, 'Select Line first...');
		if (!universeId) {
			resetSelect(manufacturerSelect, 'Select Universe first...');
			return;
		}

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_manufacturers&universe_id=${universeId}`,
		)
			.then((res) => res.json())
			.then((data) =>
				populateSelect(manufacturerSelect, data, 'Select Manufacturer...'),
			);
	};

	const loadLines = (manId) => {
		lineSelect.innerHTML = '<option>Loading...</option>';
		lineSelect.disabled = true;
		resetSelect(toySelect, 'Select Line first...');
		const uniId = universeSelect.value;
		if (!manId) return;

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_lines&manufacturer_id=${manId}&universe_id=${uniId}`,
		)
			.then((res) => res.json())
			.then((data) => populateSelect(lineSelect, data, 'Select Line...'));
	};

	const loadToys = (lineId) => {
		toySelect.innerHTML = '<option>Loading...</option>';
		toySelect.disabled = true;
		if (!lineId) return;

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toys&line_id=${lineId}`,
		)
			.then((res) => res.json())
			.then((data) =>
				populateSelect(toySelect, data, 'Select Toy / Set...'),
			);
	};

	const loadParts = (toyId) => {
		// Hvis vi skifter toy, henter vi nye dele
		availableParts = [];
		if (!toyId) {
			refreshExistingRows();
			return;
		}
		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${toyId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				availableParts = data;
				refreshExistingRows();
			});
	};

	// Event listeners
	if (universeSelect) {
		universeSelect.addEventListener('change', (e) =>
			loadManufacturers(e.target.value),
		);
		// Edit mode fix: Kun load hvis listen er tom
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
	if (toySelect)
		toySelect.addEventListener('change', (e) => loadParts(e.target.value));
	if (btnAddItem) btnAddItem.addEventListener('click', () => addItemRow());

	// Ajax submit
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
				.then((response) => response.text())
				.then((html) => {
					const modalContent = form.closest('.modal-content');
					if (modalContent) {
						modalContent.innerHTML = html;
						if (typeof App.initMediaUploads === 'function')
							App.initMediaUploads();
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

// --- DEL 2: MEDIA UPLOAD LOGIK (Trin 3 - Add/Manage Photos) ---
App.initMediaUploads = function () {
	console.log('Initializing Media Uploader...');

	const containerEl = document.getElementById('media-upload-container');

	// 1. HELPERS
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