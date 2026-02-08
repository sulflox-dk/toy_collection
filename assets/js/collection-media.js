/**
 * MEDIA UPLOAD LOGIC
 */

App.initMediaUploads = function () {
	console.log('Initializing Media Uploader...');

	const containerEl = document.getElementById('media-upload-container');

	// Find Collection ID (fra den første upload knap, som altid er parent)
	// Dette virker fordi data-id på 'collection_parent' upload knappen er selve toy id'et
	let collectionId = null;
	const parentUploadBtn = containerEl.querySelector(
		'.upload-input[data-context="collection_parent"]',
	);
	if (parentUploadBtn) {
		collectionId = parentUploadBtn.dataset.id;
	}

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

		// --- Hover effekt p� billedet ---
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
				// UPDATE: Tjekker nu for b�de collection_parent OG catalog_parent
				let pInput = containerEl.querySelector(
					'.upload-input[data-context="collection_parent"]',
				);
				if (!pInput) {
					pInput = containerEl.querySelector(
						'.upload-input[data-context="catalog_parent"]',
					);
				}

				if (pInput) {
					const pContainer = document.getElementById(
						`preview-parent-${pInput.dataset.id}`,
					);
					if (pContainer)
						mediaData.parent.forEach((img) =>
							createMediaRow(pContainer, img),
						);
				}
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
					// ... (din eksisterende kode der opdaterer modalen) ...
					const viewType =
						context === 'collection_parent' ||
						context === 'catalog_parent'
							? 'parent'
							: 'child';
					const container = document.getElementById(
						`preview-${viewType}-${id}`,
					);
					if (container) createMediaRow(container, data);

					// NYT: Opdater kortet i baggrunden!
					if (
						collectionId &&
						window.CollectionMgr &&
						typeof CollectionMgr.refreshItem === 'function'
					) {
						CollectionMgr.refreshItem(collectionId);
					}
				} else {
					alert('Upload failed: ' + (data.error || 'Unknown error'));
				}
			})
			.catch((err) => {
				console.error(err);
				alert('Upload error occurred');
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

	// NYT: Prøv at finde collection ID fra modalen, før vi sletter
	const parentBtn = document.querySelector(
		'.upload-input[data-context="collection_parent"]',
	);
	const collectionId = parentBtn ? parentBtn.dataset.id : null;

	fetch(
		`${App.baseUrl}?module=Collection&controller=Api&action=delete_media&id=${mediaId}`,
		{ method: 'POST' },
	)
		.then((res) => res.json())
		.then((data) => {
			if (data.success) {
				const row = btnElement.closest('.media-preview-row');
				row.style.opacity = '0';
				setTimeout(() => row.remove(), 300);

				// NYT: Opdater kortet
				if (
					collectionId &&
					window.CollectionMgr &&
					typeof CollectionMgr.refreshItem === 'function'
				) {
					CollectionMgr.refreshItem(collectionId);
				}
			} else {
				alert('Error deleting photo: ' + (data.error || 'Unknown error'));
			}
		})
		.catch((err) => console.error('Delete request failed', err));
};
