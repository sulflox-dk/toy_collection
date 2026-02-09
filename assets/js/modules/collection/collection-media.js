/**
 * Collection Media Module
 * Handles media upload, display, and management
 */
const CollectionMedia = {
    /**
     * Available media tags
     */
    availableMediaTags: [],

    /**
     * Current collection/entity ID
     */
    collectionId: null,

    /**
     * Initialize media module
     */
    init() {
        console.log('Initializing Collection Media Module...');

        const containerEl = document.getElementById('media-upload-container');
        if (!containerEl) return;

        // Find collection ID
        this.findCollectionId();

        // Parse tags
        if (containerEl.dataset.tags) {
            try {
                this.availableMediaTags = JSON.parse(containerEl.dataset.tags);
                window.availableMediaTags = this.availableMediaTags; // For backward compatibility
            } catch (e) {
                console.error('Failed to parse tags:', e);
            }
        }

        // Render existing media
        this.renderExistingMedia(containerEl);

        // Attach upload handlers
        this.attachUploadHandlers();
    },

    /**
     * Find collection/entity ID from various sources
     */
    findCollectionId() {
        // Method 1: From hidden input in modal form
        const modalForm = document.querySelector('#appModal form');
        if (modalForm) {
            const idInput = modalForm.querySelector('input[name="id"]');
            if (idInput) {
                this.collectionId = idInput.value;
                console.log('Media: Found ID from form input:', this.collectionId);
                return;
            }
        }

        // Method 2: From upload button dataset
        let parentUploadBtn = document.querySelector('.upload-input[data-context="collection_parent"]');
        if (!parentUploadBtn) {
            parentUploadBtn = document.querySelector('.upload-input[data-context="catalog_parent"]');
        }

        if (parentUploadBtn && parentUploadBtn.dataset.id) {
            this.collectionId = parentUploadBtn.dataset.id;
            console.log('Media: Found ID from upload button:', this.collectionId);
        }
    },

    /**
     * Render existing media from dataset
     * @param {HTMLElement} containerEl - Media container element
     */
    renderExistingMedia(containerEl) {
        if (!containerEl.dataset.existingMedia) return;

        try {
            const mediaData = JSON.parse(containerEl.dataset.existingMedia);

            // Render parent images
            if (mediaData.parent && mediaData.parent.length > 0) {
                let pInput = containerEl.querySelector('.upload-input[data-context="collection_parent"]');
                if (!pInput) {
                    pInput = containerEl.querySelector('.upload-input[data-context="catalog_parent"]');
                }

                if (pInput) {
                    const pContainer = document.getElementById(`preview-parent-${pInput.dataset.id}`);
                    if (pContainer) {
                        pContainer.innerHTML = '';
                        mediaData.parent.forEach(img => this.createMediaRow(pContainer, img));
                    }
                }
            }

            // Render child images
            if (mediaData.items) {
                mediaData.items.forEach(item => {
                    const cContainer = document.getElementById(`preview-child-${item.id}`);
                    if (cContainer && item.images) {
                        cContainer.innerHTML = '';
                        item.images.forEach(img => this.createMediaRow(cContainer, img));
                    }
                });
            }
        } catch (e) {
            console.error('Failed to parse existing media:', e);
        }
    },

    /**
     * Attach upload handlers to all upload inputs
     */
    attachUploadHandlers() {
        document.querySelectorAll('.upload-input').forEach(input => {
            // Clone to remove old listeners
            const newClone = input.cloneNode(true);
            input.parentNode.replaceChild(newClone, input);

            newClone.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    Array.from(this.files).forEach(file => {
                        CollectionMedia.handleUpload(file, this.dataset.context, this.dataset.id, this);
                    });
                }
            });
        });
    },

    /**
     * Handle file upload
     * @param {File} file - File to upload
     * @param {string} context - Context (collection_parent, collection_child, etc.)
     * @param {string} id - Entity ID
     * @param {HTMLElement} inputElement - Input element
     */
    async handleUpload(file, context, id, inputElement) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('target_context', context);
        formData.append('target_id', id);

        const labelBtn = inputElement.parentElement;
        const icon = labelBtn.querySelector('i');
        const originalIconClass = icon ? icon.className : '';

        try {
            // Show loading state
            if (icon) icon.className = 'fas fa-spinner fa-spin me-1';
            labelBtn.classList.add('disabled');

            const response = await ApiClient.post(
                ApiClient.buildModuleUrl('Media', 'Media', 'upload'),
                formData
            );

            if (response.success) {
                // Determine container
                const isParent = context === 'collection_parent' || context === 'catalog_parent';
                const viewType = isParent ? 'parent' : 'child';
                const containerId = `preview-${viewType}-${id}`;
                const container = document.getElementById(containerId);

                if (container) {
                    this.createMediaRow(container, response.media_data || response);
                }

                // Refresh background card
                this.refreshBackgroundCard();
                
                UiHelper.showSuccess('Image uploaded successfully');
            } else {
                UiHelper.showError(response.error || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            UiHelper.showError('Upload error occurred');
        } finally {
            // Reset button state
            if (icon) icon.className = originalIconClass || 'fas fa-plus me-1';
            labelBtn.classList.remove('disabled');
            inputElement.value = '';
        }
    },

    /**
     * Create media row
     * @param {HTMLElement} container - Container element
     * @param {Object} data - Media data
     */
    createMediaRow(container, data) {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'media-preview-row fade-in-row';

        const isMainChecked = data.is_main == 1 ? 'checked' : '';
        const radioName = `main_image_${data.context || 'collection'}_${data.entity_id || this.collectionId}`;

        rowDiv.innerHTML = `
            <div class="media-img-container">
                <div class="media-img-frame">
                    <img src="${data.file_path}" alt="Toy Photo">
                </div>
                
                <div class="form-check form-check-sm user-select-none mt-2">
                    <input class="form-check-input media-main-input" type="radio" 
                           name="${radioName}" 
                           id="main_${data.media_id}" ${isMainChecked}>
                    <label class="form-check-label small text-muted" for="main_${data.media_id}" style="cursor: pointer;">
                        Main Image
                    </label>
                </div>

                <button type="button" 
                        class="btn btn-sm btn-outline-secondary px-2 delete-btn-general delete-photo-btn" 
                        data-media-id="${data.media_id}">
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
                            ${this.buildTagPills(data.tags)}
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

        // Attach event listeners
        this.attachMediaRowListeners(rowDiv, data.media_id);
    },

    /**
     * Attach event listeners to media row
     * @param {HTMLElement} rowDiv - Row element
     * @param {number} mediaId - Media ID
     */
    attachMediaRowListeners(rowDiv, mediaId) {
        // Tag pills
        rowDiv.querySelectorAll('.tag-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                if (this.classList.contains('bg-dark')) {
                    this.classList.replace('bg-dark', 'bg-light');
                    this.classList.replace('text-white', 'text-dark');
                } else {
                    this.classList.replace('bg-light', 'bg-dark');
                    this.classList.replace('text-dark', 'text-white');
                }
                CollectionMedia.saveMetadata(mediaId, rowDiv);
            });
        });

        // Comment input
        const commentInput = rowDiv.querySelector('.media-comment-input');
        if (commentInput) {
            commentInput.addEventListener('change', () => {
                this.saveMetadata(mediaId, rowDiv);
            });
        }

        // Main image radio
        const mainRadio = rowDiv.querySelector('.media-main-input');
        if (mainRadio) {
            mainRadio.addEventListener('change', function() {
                if (this.checked) {
                    // Uncheck others in group
                    const groupName = this.name;
                    document.querySelectorAll(`input[name="${groupName}"]`).forEach(cb => {
                        if (cb !== this) cb.checked = false;
                    });
                    CollectionMedia.saveMetadata(mediaId, rowDiv);
                }
            });
        }

        // Delete button
        const deleteBtn = rowDiv.querySelector('.delete-photo-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                this.deleteMedia(mediaId, deleteBtn);
            });
        }

        // Image hover zoom
        const imgEl = rowDiv.querySelector('.media-img-frame img');
        if (imgEl) {
            this.attachImageZoom(imgEl);
        }
    },

    /**
     * Build tag pills HTML
     * @param {Array} activeTags - Active tags
     * @returns {string} HTML string
     */
    buildTagPills(activeTags) {
        if (!this.availableMediaTags || this.availableMediaTags.length === 0) return '';

        return this.availableMediaTags.map(tag => {
            const isActive = activeTags && activeTags.some(t => t.id == tag.id);
            const bgClass = isActive ? 'bg-dark text-white' : 'bg-light text-dark';
            
            return `
                <span class="badge rounded-pill ${bgClass} border tag-pill" 
                      data-id="${tag.id}" 
                      style="cursor: pointer;">
                    ${UiHelper.escapeHtml(tag.tag_name)}
                </span>
            `;
        }).join('');
    },

    /**
     * Save media metadata
     * @param {number} mediaId - Media ID
     * @param {HTMLElement} rowElement - Row element
     */
    async saveMetadata(mediaId, rowElement) {
        const commentInput = rowElement.querySelector('.media-comment-input');
        const mainInput = rowElement.querySelector('.media-main-input');
        const indicator = rowElement.querySelector('.save-indicator');
        const activePills = rowElement.querySelectorAll('.tag-pill.bg-dark');
        
        const selectedTags = Array.from(activePills).map(pill => pill.dataset.id);
        const isMain = mainInput ? mainInput.checked : false;

        const data = {
            media_id: mediaId,
            user_comment: commentInput ? commentInput.value : '',
            is_main: isMain ? 1 : 0,
            'tags[]': selectedTags
        };

        try {
            const response = await ApiClient.post(
                ApiClient.buildModuleUrl('Media', 'Media', 'update_metadata'),
                data
            );

            if (response.success) {
                // Show save indicator
                if (indicator) {
                    indicator.classList.remove('opacity-0');
                    setTimeout(() => indicator.classList.add('opacity-0'), 2000);
                }

                // Refresh card if main image changed
                if (isMain) {
                    this.refreshBackgroundCard();
                }
            }
        } catch (error) {
            console.error('Save metadata failed:', error);
            UiHelper.showError('Failed to save metadata');
        }
    },

    /**
     * Delete media file
     * @param {number} mediaId - Media ID
     * @param {HTMLElement} btnElement - Delete button element
     */
    async deleteMedia(mediaId, btnElement) {
        const confirmed = await UiHelper.confirmDelete('this photo');
        
        if (!confirmed) return;

        try {
            const response = await CollectionApi.deleteMedia(mediaId);

            if (response.success) {
                // Remove row visually
                const row = btnElement.closest('.media-preview-row');
                if (row) {
                    UiHelper.fadeOut(row, 300);
                    setTimeout(() => row.remove(), 300);
                }

                // Refresh background card
                this.refreshBackgroundCard();
                
                UiHelper.showSuccess('Photo deleted successfully');
            } else {
                UiHelper.showError(response.error || 'Failed to delete photo');
            }
        } catch (error) {
            console.error('Delete media failed:', error);
            UiHelper.showError('Failed to delete photo');
        }
    },

    /**
     * Refresh background card/grid item
     */
    refreshBackgroundCard() {
        if (!this.collectionId) return;

        // Try Collection Manager
        if (window.CollectionManager && typeof CollectionManager.refreshItem === 'function') {
            console.log('Media: Refreshing Collection item', this.collectionId);
            CollectionManager.refreshItem(this.collectionId);
        }
        // Try Master Toy Manager
        else if (window.MasterToyMgr && typeof MasterToyMgr.refreshItem === 'function') {
            console.log('Media: Refreshing Master Toy item', this.collectionId);
            MasterToyMgr.refreshItem(this.collectionId);
        }
    },

    /**
     * Attach image zoom on hover
     * @param {HTMLElement} imgEl - Image element
     */
    attachImageZoom(imgEl) {
        imgEl.style.cursor = 'zoom-in';
        let hoverTimeout;

        imgEl.addEventListener('mouseenter', function() {
            hoverTimeout = setTimeout(() => {
                const bigImg = document.createElement('img');
                bigImg.src = this.src;
                bigImg.className = 'media-hover-zoom';
                bigImg.id = 'active-hover-zoom';
                document.body.appendChild(bigImg);
            }, 400);
        });

        imgEl.addEventListener('mouseleave', function() {
            clearTimeout(hoverTimeout);
            const bigImg = document.getElementById('active-hover-zoom');
            if (bigImg) bigImg.remove();
        });
    },

    /**
     * Finish create flow (called from wizard)
     */
    finishCreateFlow() {
        // Close modal
        const modalEl = document.getElementById('appModal');
        if (modalEl) {
            if (document.activeElement) {
                document.activeElement.blur();
            }

            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Cleanup backdrop
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(bd => bd.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
            }, 300);
        }

        // Refresh appropriate view
        if (window.CollectionManager && document.getElementById('collectionGridContainer')) {
            console.log('Wizard finished: Reloading collection list...');
            CollectionManager.loadPage(1);
        } else if (window.MasterToyMgr && document.getElementById('masterToyGridContainer')) {
            console.log('Wizard finished: Reloading master toy list...');
            MasterToyMgr.loadPage(1);
        } else {
            console.log('Wizard finished: Reloading page...');
            window.location.reload();
        }
    }
};

// Make globally available
window.CollectionMedia = CollectionMedia;

// Legacy compatibility
window.App = window.App || {};
App.initMediaUploads = () => CollectionMedia.init();
App.deleteMedia = (mediaId, btnElement) => CollectionMedia.deleteMedia(mediaId, btnElement);
App.finishCreateFlow = () => CollectionMedia.finishCreateFlow();
