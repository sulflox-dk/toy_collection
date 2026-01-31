/**
 * Collection Module Logic
 */

// --- DEL 1: FORM LOGIK (Trin 2 - Add Toy & Items) ---
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

	// --- DROPDOWNS ---
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
			`${App.baseUrl}?module=Collection&controller=Api&action=get_items&line_id=${lineId}`,
		)
			.then((res) => res.json())
			.then((data) =>
				populateSelect(toySelect, data, 'Select Toy / Set...'),
			);
	};

	const loadParts = (toyId) => {
		availableParts = [];
		if (!toyId) {
			refreshExistingRows();
			return;
		}
		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=get_toy_parts&master_toy_id=${toyId}`,
		)
			.then((res) => res.json())
			.then((data) => {
				availableParts = data;
				refreshExistingRows();
			});
	};

	const refreshExistingRows = () => {
		const selects = container.querySelectorAll('.item-part-select');
		selects.forEach((select) => {
			const currentVal = select.value;
			let options =
				availableParts.length > 0
					? '<option value="">Select Item...</option>'
					: '<option value="">Unknown Parts (Select Toy above first)</option>';
			if (availableParts.length > 0)
				availableParts.forEach((part) => {
					options += `<option value="${part.id}">${part.name} (${part.type})</option>`;
				});
			select.innerHTML = options;
			select.value = availableParts.some((p) => p.id == currentVal)
				? currentVal
				: '';
		});
	};

	const addItemRow = async () => {
		if (availableParts.length === 0 && toySelect.value) {
			try {
				const res = await fetch(
					`${App.baseUrl}?module=Collection&controller=Api&action=get_toy_parts&master_toy_id=${toySelect.value}`,
				);
				availableParts = await res.json();
			} catch (e) {
				console.error(e);
			}
		}
		const index = rowCount;
		rowCount++;
		const clone = template.content.cloneNode(true);

		clone.querySelectorAll('[name*="INDEX"]').forEach((el) => {
			el.name = el.name.replace('INDEX', index);
			if (el.id) el.id = el.id.replace('INDEX', index);
		});
		clone.querySelectorAll('[for*="INDEX"]').forEach((el) => {
			el.setAttribute('for', el.getAttribute('for').replace('INDEX', index));
		});
		clone.querySelector('.row-number').textContent = rowCount;

		const partSelect = clone.querySelector('.item-part-select');
		if (availableParts.length > 0) {
			let options = '<option value="">Select Item...</option>';
			availableParts.forEach((part) => {
				options += `<option value="${part.id}">${part.name} (${part.type})</option>`;
			});
			partSelect.innerHTML = options;
		} else {
			partSelect.innerHTML =
				'<option value="">Unknown Parts (Select Toy above first)</option>';
		}

		clone
			.querySelector('.remove-row-btn')
			.addEventListener('click', function (e) {
				e.target.closest('.child-item-row').remove();
				updateCount();
			});
		container.appendChild(clone);
		updateCount();
		const newRow = container.lastElementChild;
		if (newRow)
			newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
	};

	const updateCount = () => {
		const count = container.querySelectorAll('.child-item-row').length;
		if (countBadge) countBadge.textContent = `${count} items`;
	};

	if (universeSelect) {
		universeSelect.addEventListener('change', (e) =>
			loadManufacturers(e.target.value),
		);
		if (universeSelect.value) loadManufacturers(universeSelect.value);
	}
	if (manufacturerSelect)
		manufacturerSelect.addEventListener('change', (e) =>
			loadLines(e.target.value),
		);
	if (lineSelect)
		lineSelect.addEventListener('change', (e) => loadToys(e.target.value));
	if (toySelect)
		toySelect.addEventListener('change', (e) => loadParts(e.target.value));
	if (btnAddItem) btnAddItem.addEventListener('click', addItemRow);

	// --- FORM SUBMIT VIA AJAX ---
	const form = document.getElementById('addToyForm');
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			const rowCount = container.querySelectorAll('.child-item-row').length;
			if (rowCount === 0) {
				alert(
					'You must add at least one Item (Figure/Part) to this set before saving.',
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
					} else {
						alert('Error: Could not update modal. Please refresh.');
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

// --- DEL 2: MEDIA UPLOAD LOGIK (Trin 3 - Add Photos) ---
App.initMediaUploads = function () {
	console.log('Initializing Media Uploader...');

	const containerEl = document.getElementById('media-upload-container');
	if (containerEl && containerEl.dataset.tags) {
		try {
			window.availableMediaTags = JSON.parse(containerEl.dataset.tags);
		} catch (e) {
			console.error('Failed to parse tags JSON', e);
		}
	}

	const inputs = document.querySelectorAll('.upload-input');
	inputs.forEach((input) => {
		input.addEventListener('change', function () {
			if (this.files && this.files.length > 0) {
				Array.from(this.files).forEach((file) => {
					handleUpload(file, this.dataset.context, this.dataset.id, this);
				});
			}
		});
	});

	const handleUpload = (file, context, id, inputElement) => {
		const formData = new FormData();
		formData.append('file', file);
		formData.append('target_context', context);
		formData.append('target_id', id);

		const labelBtn = inputElement.parentElement;
		const icon = labelBtn.querySelector('i');
		const originalIconClass = icon ? icon.className : 'fas fa-plus me-1';
		if (icon) icon.className = 'fas fa-spinner fa-spin me-1';
		labelBtn.classList.add('disabled');

		fetch(`${App.baseUrl}?module=Media&controller=Media&action=upload`, {
			method: 'POST',
			body: formData,
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.success) {
					let viewType =
						context === 'collection_parent' ? 'parent' : 'child';
					const previewId = `preview-${viewType}-${id}`;
					const container = document.getElementById(previewId);
					if (container) createMediaRow(container, data);
				} else {
					alert('Upload failed: ' + (data.error || 'Unknown error'));
				}
			})
			.catch((error) => {
				console.error('Error:', error);
				alert('System error during upload');
			})
			.finally(() => {
				if (icon) icon.className = originalIconClass;
				labelBtn.classList.remove('disabled');
				inputElement.value = '';
			});
	};

	// HJÆLPEFUNKTION: BYG HTML FOR BILLED-RÆKKE
	const createMediaRow = (container, data) => {
		const rowDiv = document.createElement('div');
		rowDiv.className =
			'd-flex gap-4 align-items-start bg-white p-3 border rounded shadow-sm fade-in-row mb-3';

		// --- VENSTRE KOLONNE: BILLEDE + TOOLS ---
		const imgCol = document.createElement('div');
		imgCol.className = 'd-flex flex-column align-items-center gap-2';
		imgCol.style.width = '120px';
		imgCol.style.flexShrink = '0';

		imgCol.innerHTML = `
            <div style="width: 100px; height: 100px; background: url('${data.file_path}') center/cover no-repeat; border-radius: 4px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"></div>
            
            <div class="form-check form-check-sm user-select-none mt-1">
                <input class="form-check-input media-main-input" type="radio" name="main_image_${data.media_id}" id="main_${data.media_id}" style="cursor: pointer;">
                <label class="form-check-label small text-muted" for="main_${data.media_id}" style="cursor: pointer;">Main Image</label>
            </div>

            <span class="badge bg-success opacity-0 transition-opacity save-indicator" style="font-weight: normal; font-size: 0.65rem;">
                <i class="fas fa-check me-1"></i>Saved
            </span>
        `;

		// --- HØJRE KOLONNE: INPUTS ---
		const infoCol = document.createElement('div');
		infoCol.className = 'flex-grow-1';

		// 1. PILLS HTML
		let tagPillsHtml = '<div class="d-flex flex-wrap gap-2 tag-container">';
		if (window.availableMediaTags) {
			window.availableMediaTags.forEach((tag) => {
				// Vi beholder 'border' altid for at undgå at knappen ændrer størrelse
				tagPillsHtml += `
                    <span class="badge rounded-pill bg-light text-dark border user-select-none tag-pill px-3 py-2" 
                          style="cursor: pointer; font-weight: normal;" 
                          data-id="${tag.id}">
                        ${tag.tag_name}
                    </span>`;
			});
		}
		tagPillsHtml += '</div>';

		infoCol.innerHTML = `
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label small text-muted mb-2">Tags</label>
                    ${tagPillsHtml}
                </div>
                <div class="col-md-12">
                    <label class="form-label small text-muted mb-2">Comments</label>
                    <textarea class="form-control media-comment-input" rows="2" placeholder="Describe photo (e.g. 'Damage detail')"></textarea>
                </div>
            </div>
        `;

		rowDiv.appendChild(imgCol);
		rowDiv.appendChild(infoCol);
		container.appendChild(rowDiv);

		// --- EVENT LISTENERS ---

		// 1. PILLS TOGGLE (Blå/Sort)
		const pills = rowDiv.querySelectorAll('.tag-pill');
		pills.forEach((pill) => {
			pill.addEventListener('click', function () {
				if (this.classList.contains('bg-dark')) {
					// Gør inaktiv
					this.classList.replace('bg-dark', 'bg-light');
					this.classList.replace('text-white', 'text-dark');
					// 'border' forbliver
				} else {
					// Gør aktiv (Sort/Mørk)
					this.classList.replace('bg-light', 'bg-dark');
					this.classList.replace('text-dark', 'text-white');
					// 'border' forbliver
				}
				saveMetadata(data.media_id, rowDiv);
			});
		});

		// 2. KOMMENTAR
		const commentInput = rowDiv.querySelector('.media-comment-input');
		commentInput.addEventListener('change', () =>
			saveMetadata(data.media_id, rowDiv),
		);

		// 3. MAIN CHECKBOX LOGIK
		const mainInput = rowDiv.querySelector('.media-main-input');
		mainInput.addEventListener('change', function () {
			if (this.checked) {
				// Uncheck visuelt alle andre checkboxes i samme container (samme toy/item)
				const allCheckboxes =
					container.querySelectorAll('.media-main-input');
				allCheckboxes.forEach((cb) => {
					if (cb !== this) cb.checked = false;
				});
				saveMetadata(data.media_id, rowDiv);
			}
		});
	};

	const saveMetadata = (mediaId, rowElement) => {
		const commentInput = rowElement.querySelector('.media-comment-input');
		const mainInput = rowElement.querySelector('.media-main-input');
		const indicator = rowElement.querySelector('.save-indicator');

		// Hent aktive tags (dem med bg-dark)
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
};
