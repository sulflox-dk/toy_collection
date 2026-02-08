/**
 * MEDIA UPLOAD LOGIC
 */

App.initMediaUploads = function () {
	console.log('Initializing Media Uploader...');

	const containerEl = document.getElementById('media-upload-container');
	if (!containerEl) return;

	// --- 1. FIND ID (Robust metode) ---
	let collectionId = null;
	const modalForm = document.querySelector('#appModal form');

	// Prøv at finde ID fra det skjulte input i modalen
	if (modalForm) {
		const idInput = modalForm.querySelector('input[name="id"]');
		if (idInput) collectionId = idInput.value;
	}

	// Fallback: Find ID fra upload knappen
	if (!collectionId) {
		const parentUploadBtn = containerEl.querySelector(
			'.upload-input[data-context="collection_parent"]',
		);
		if (parentUploadBtn && parentUploadBtn.dataset.id) {
			collectionId = parentUploadBtn.dataset.id;
		}
	}

	console.log('Media: Init fundet Collection ID:', collectionId);

	// Hjælpefunktion: Opdater kortet bagved
	const refreshBackgroundCard = () => {
		if (
			collectionId &&
			typeof CollectionMgr !== 'undefined' &&
			typeof CollectionMgr.refreshItem === 'function'
		) {
			console.log('Media: Refreshing item ' + collectionId);
			CollectionMgr.refreshItem(collectionId);
		}
	};

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
                <span class="badge rounded-pill ${bgClass} border tag-pill" data-id="${tag.id}" style="cursor: pointer;">
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
		const isMain = mainInput.checked;

		const formData = new FormData();
		formData.append('media_id', mediaId);
		formData.append('user_comment', commentInput ? commentInput.value : '');
		formData.append('is_main', isMain ? 1 : 0);
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
					if (indicator) {
						indicator.classList.remove('opacity-0');
						setTimeout(() => indicator.classList.add('opacity-0'), 2000);
					}
					// Hvis vi har ændret hovedbilledet, opdater kortet!
					if (isMain) {
						refreshBackgroundCard();
					}
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
                           name="main_image_${data.context || 'collection'}_${data.entity_id || collectionId}" 
                           id="main_${data.media_id}" ${isMainChecked}>
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

		if (container) container.appendChild(rowDiv);

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

		const commentInput = rowDiv.querySelector('.media-comment-input');
		if (commentInput) {
			commentInput.addEventListener('change', () =>
				saveMetadata(data.media_id, rowDiv),
			);
		}

		const mainRadio = rowDiv.querySelector('.media-main-input');
		if (mainRadio) {
			mainRadio.addEventListener('change', function () {
				if (this.checked) {
					// Reset andre radio buttons i samme gruppe visuelt (hvis nødvendigt)
					const groupName = this.name;
					document
						.querySelectorAll(`input[name="${groupName}"]`)
						.forEach((cb) => {
							if (cb !== this) cb.checked = false;
						});
					saveMetadata(data.media_id, rowDiv);
				}
			});
		}

		// --- Hover effekt ---
		const imgEl = rowDiv.querySelector('.media-img-frame img');
		if (imgEl) {
			imgEl.style.cursor = 'zoom-in';
			let hoverTimeout;

			imgEl.addEventListener('mouseenter', function () {
				hoverTimeout = setTimeout(() => {
					const bigImg = document.createElement('img');
					bigImg.src = this.src;
					bigImg.className = 'media-hover-zoom';
					bigImg.id = 'active-hover-zoom';
					document.body.appendChild(bigImg);
				}, 400);
			});

			imgEl.addEventListener('mouseleave', function () {
				clearTimeout(hoverTimeout);
				const bigImg = document.getElementById('active-hover-zoom');
				if (bigImg) bigImg.remove();
			});
		}
	};

	// 2. PARSE EXISTING DATA
	if (containerEl.dataset.tags) {
		try {
			window.availableMediaTags = JSON.parse(containerEl.dataset.tags);
		} catch (e) {
			console.error('Failed to parse tags', e);
		}
	}

	if (containerEl.dataset.existingMedia) {
		try {
			const mediaData = JSON.parse(containerEl.dataset.existingMedia);

			// Render Parent Images
			if (mediaData.parent && mediaData.parent.length > 0) {
				let pInput = containerEl.querySelector(
					'.upload-input[data-context="collection_parent"]',
				);
				if (!pInput)
					pInput = containerEl.querySelector(
						'.upload-input[data-context="catalog_parent"]',
					);

				if (pInput) {
					const pContainer = document.getElementById(
						`preview-parent-${pInput.dataset.id}`,
					);
					if (pContainer) {
						pContainer.innerHTML = ''; // Clear first
						mediaData.parent.forEach((img) =>
							createMediaRow(pContainer, img),
						);
					}
				}
			}

			// Render Child Images
			if (mediaData.items) {
				mediaData.items.forEach((item) => {
					const cContainer = document.getElementById(
						`preview-child-${item.id}`,
					);
					if (cContainer && item.images) {
						cContainer.innerHTML = ''; // Clear first
						item.images.forEach((img) => createMediaRow(cContainer, img));
					}
				});
			}
		} catch (e) {
			console.error('Failed to parse existing media', e);
		}
	}

	// 3. UPLOAD HANDLER
	const handleUpload = (file, context, id, inputElement) => {
		const formData = new FormData();
		formData.append('file', file);
		formData.append('target_context', context);
		formData.append('target_id', id);

		const labelBtn = inputElement.parentElement;
		const icon = labelBtn.querySelector('i');
		const originalIconClass = icon ? icon.className : '';

		if (icon) icon.className = 'fas fa-spinner fa-spin me-1';
		labelBtn.classList.add('disabled');

		fetch(`${App.baseUrl}?module=Media&controller=Media&action=upload`, {
			method: 'POST',
			body: formData,
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					// Bestem container ID
					const isParent =
						context === 'collection_parent' ||
						context === 'catalog_parent';
					const viewType = isParent ? 'parent' : 'child';
					const containerId = `preview-${viewType}-${id}`;
					const container = document.getElementById(containerId);

					if (container) {
						createMediaRow(container, data.media_data || data); // Brug data objektet
					}

					// Opdater kortet bagved
					refreshBackgroundCard();
				} else {
					alert('Upload failed: ' + (data.error || 'Unknown error'));
				}
			})
			.catch((err) => {
				console.error(err);
				alert('Upload error occurred');
			})
			.finally(() => {
				if (icon) icon.className = originalIconClass || 'fas fa-plus me-1';
				labelBtn.classList.remove('disabled');
				inputElement.value = '';
			});
	};

	// Attach listeners
	document.querySelectorAll('.upload-input').forEach((input) => {
		// Fjern gamle listeners for at undgå double-submit (hvis funktionen kaldes flere gange)
		const newClone = input.cloneNode(true);
		input.parentNode.replaceChild(newClone, input);

		newClone.addEventListener('change', function () {
			if (this.files) {
				Array.from(this.files).forEach((file) =>
					handleUpload(file, this.dataset.context, this.dataset.id, this),
				);
			}
		});
	});
};

App.deleteMedia = function (mediaId, btnElement) {
	if (!confirm('Are you sure you want to delete this photo?')) return;

	// --- 1. FIND ID (ROBUST METODE) ---
	let collectionId = null;

	// Metode A: Kig efter den skjulte input 'id' i modalens form (Mest sikker)
	const modalForm = document.querySelector('#appModal form');
	if (modalForm) {
		const idInput = modalForm.querySelector('input[name="id"]');
		if (idInput) collectionId = idInput.value;
	}

	// Metode B: Kig efter upload-knappen (Fallback)
	if (!collectionId) {
		const parentUploadBtn = document.querySelector(
			'.upload-input[data-context="collection_parent"]',
		);
		if (parentUploadBtn && parentUploadBtn.dataset.id) {
			collectionId = parentUploadBtn.dataset.id;
		}
	}

	console.log('DEBUG: deleteMedia - Fundet Collection ID:', collectionId);

	// --- 2. UDFØR SLETNING ---
	fetch(
		`${App.baseUrl}?module=Collection&controller=Api&action=delete_media&id=${mediaId}`,
		{ method: 'POST' },
	)
		.then((res) => res.json())
		.then((data) => {
			if (data.success) {
				// Fjern rækken visuelt
				const row = btnElement.closest('.media-preview-row');
				if (row) {
					row.style.opacity = '0';
					setTimeout(() => row.remove(), 300);
				}

				// --- 3. OPDATER KORTET BAGVED ---
				// Vi bruger 'typeof' for at tjekke om Manageren findes, da den måske ikke er direkte på window-objektet
				if (
					collectionId &&
					typeof CollectionMgr !== 'undefined' &&
					typeof CollectionMgr.refreshItem === 'function'
				) {
					console.log('Media: Kalder refreshItem for ID:', collectionId);
					CollectionMgr.refreshItem(collectionId);
				} else {
					console.error(
						'Media: KAN IKKE OPDATERE KORTET! Mangler ID eller Manager. ID:',
						collectionId,
						'Manager Type:',
						typeof CollectionMgr,
					);
				}
			} else {
				alert('Error deleting photo: ' + (data.error || 'Unknown error'));
			}
		})
		.catch((err) => console.error('Delete request failed', err));
};

/**
 * Håndterer "Finish" knappen i Create Wizard (Step 3)
 * Lukker modalen og opdaterer listen/siden i stedet for at redirecte til dashboard.
 */
App.finishCreateFlow = function () {
	// 1. Luk modalen pænt
	const modalEl = document.getElementById('appModal');
	if (modalEl) {
		const modal = bootstrap.Modal.getInstance(modalEl);
		if (modal) modal.hide();

		// Sikkerhedsnet: Fjern backdrop manuelt hvis Bootstrap driller
		setTimeout(() => {
			const backdrops = document.querySelectorAll('.modal-backdrop');
			backdrops.forEach((bd) => bd.remove());
			document.body.classList.remove('modal-open');
			document.body.style.overflow = '';
		}, 300);
	}

	// 2. Opdater visningen
	if (
		typeof CollectionMgr !== 'undefined' &&
		document.getElementById('collectionGridContainer')
	) {
		// Hvis vi står på Collection-listen: Reload grid (side 1) så det nye item vises
		console.log('Wizard finished: Reloading list view...');
		CollectionMgr.loadPage(1);
	} else {
		// Hvis vi står på Dashboard eller andet sted: Reload hele siden
		console.log('Wizard finished: Reloading page...');
		window.location.reload();
	}
};
