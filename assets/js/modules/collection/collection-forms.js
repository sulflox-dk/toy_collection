/**
 * Collection Forms Module
 * Handles form operations, cascading dropdowns, and widget functionality
 */
const CollectionForms = {
    /**
     * Available master toy items for current selection
     */
    availableMasterToyItems: [],

    /**
     * Current list of all master toys (for widget)
     */
    currentMasterToysList: [],

    /**
     * Row counter for dynamic item rows
     */
    rowCount: 0,

    /**
     * Initialize forms module
     */
    init() {
        this.attachEventHandlers();
        this.initializeWidget();
        this.initializeItemRows();
        this.initializeFormSubmit();
    },

    /**
     * Attach event handlers to form elements
     */
    attachEventHandlers() {
        // Cascading dropdowns
        const universeSelect = document.getElementById('selectUniverse');
        const manufacturerSelect = document.getElementById('selectManufacturer');
        const lineSelect = document.getElementById('selectLine');
        const btnAddItem = document.getElementById('btnAddItemRow');

        if (universeSelect) {
            universeSelect.addEventListener('change', (e) => this.loadManufacturers(e.target.value));
        }

        if (manufacturerSelect) {
            manufacturerSelect.addEventListener('change', (e) => this.loadLines(e.target.value));
        }

        if (lineSelect) {
            lineSelect.addEventListener('change', (e) => this.loadToys(e.target.value));
        }

        if (btnAddItem) {
            btnAddItem.addEventListener('click', () => this.addItemRow());
        }

        // Widget search
        const widgetSearch = document.getElementById('inputToySearch');
        if (widgetSearch) {
            widgetSearch.addEventListener('input', (e) => {
                this.renderWidgetResults(e.target.value);
            });
        }

        // Widget overlay toggle
        const widgetCard = document.getElementById('masterToyDisplayCard');
        if (widgetCard) {
            widgetCard.addEventListener('click', () => {
                const overlay = document.getElementById('masterToyOverlay');
                if (overlay) {
                    overlay.classList.toggle('show');
                }
            });
        }
    },

    /**
     * Initialize widget with data from dataset
     */
    initializeWidget() {
        const widgetWrapper = document.getElementById('masterToyWidgetWrapper');
        
        if (!widgetWrapper) return;

        // Load all toys for widget
        if (widgetWrapper.dataset.allToys) {
            try {
                this.currentMasterToysList = JSON.parse(widgetWrapper.dataset.allToys);
            } catch (e) {
                console.error('Error parsing all toys:', e);
            }
        }

        // Load selected toy in edit mode
        if (widgetWrapper.dataset.selectedToy) {
            try {
                const selectedToy = JSON.parse(widgetWrapper.dataset.selectedToy);
                if (selectedToy) {
                    this.updateWidgetDisplay(selectedToy);
                    const widgetCard = document.getElementById('masterToyDisplayCard');
                    if (widgetCard) {
                        widgetCard.classList.remove('disabled');
                    }
                }
            } catch (e) {
                console.error('Error parsing selected toy:', e);
            }
        }
    },

    /**
     * Initialize existing item rows from dataset
     */
    initializeItemRows() {
        const container = document.getElementById('childItemsContainer');
        
        if (!container) return;

        // Load master toy items from dataset
        if (container.dataset.masterToyItems) {
            try {
                this.availableMasterToyItems = JSON.parse(container.dataset.masterToyItems);
            } catch (e) {
                console.error('Error parsing master toy items:', e);
            }
        }

        // Check for auto-fill trigger
        const autoAddTrigger = document.getElementById('triggerAutoAddItems');
        if (autoAddTrigger) {
            setTimeout(() => {
                console.log('Auto-add trigger detected: Adding all items...');
                this.addAllItemsFromMaster();
            }, 100);
        }
    },

    /**
     * Initialize form submit handler
     */
    initializeFormSubmit() {
        const form = document.getElementById('addToyForm');
        
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate at least one item
            const container = document.getElementById('childItemsContainer');
            if (container && container.querySelectorAll('.child-item-row').length === 0) {
                UiHelper.showError('You must add at least one item before saving.');
                const btnAddItem = document.getElementById('btnAddItemRow');
                if (btnAddItem) {
                    UiHelper.scrollTo(btnAddItem, 100);
                }
                return;
            }

            // Validate form
            if (!Validation.validateForm(form)) {
                return;
            }

            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            try {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                submitBtn.disabled = true;

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get('content-type');

                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    
                    if (data.success) {
                        this.handleSaveSuccess(data);
                    } else {
                        UiHelper.showError(data.error || 'Failed to save toy');
                    }
                } else {
                    // HTML response - replace modal content
                    const html = await response.text();
                    const modalContent = form.closest('.modal-content');
                    
                    if (modalContent) {
                        modalContent.innerHTML = html;
                        
                        // Reinitialize media uploads if needed
                        if (window.CollectionMedia && typeof CollectionMedia.init === 'function') {
                            CollectionMedia.init();
                        }
                    }
                }
            } catch (error) {
                console.error('Save error:', error);
                UiHelper.showError('Error saving toy');
            } finally {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    },

    /**
     * Load manufacturers by universe
     * @param {number} universeId - Universe ID
     */
    async loadManufacturers(universeId) {
        const manufacturerSelect = document.getElementById('selectManufacturer');
        
        if (!universeId) {
            this.resetSelect(manufacturerSelect, 'Select Manufacturer');
            this.resetSelect(document.getElementById('selectLine'), 'Select Line');
            this.resetWidget('Select Master Toy');
            return;
        }

        try {
            const manufacturers = await window.manufacturerManager.getByUniverse(universeId);
            this.populateSelect(manufacturerSelect, manufacturers, 'Select Manufacturer');
        } catch (error) {
            console.error('Failed to load manufacturers:', error);
            UiHelper.showError('Failed to load manufacturers');
        }
    },

    /**
     * Load toy lines by manufacturer
     * @param {number} manufacturerId - Manufacturer ID
     */
    async loadLines(manufacturerId) {
        const lineSelect = document.getElementById('selectLine');
        
        if (!manufacturerId) {
            this.resetSelect(lineSelect, 'Select Line');
            this.resetWidget('Select Master Toy');
            return;
        }

        try {
            const lines = await window.toyLineManager.getByManufacturer(manufacturerId);
            this.populateSelect(lineSelect, lines, 'Select Line');
        } catch (error) {
            console.error('Failed to load lines:', error);
            UiHelper.showError('Failed to load toy lines');
        }
    },

    /**
     * Load master toys by line
     * @param {number} lineId - Line ID
     */
    async loadToys(lineId) {
        const widgetCard = document.getElementById('masterToyDisplayCard');
        
        if (!lineId) {
            this.resetWidget('Select Master Toy');
            return;
        }

        try {
            const masterToys = await window.masterToyManager.getByLine(lineId);
            this.currentMasterToysList = masterToys;
            
            if (widgetCard) {
                widgetCard.classList.remove('disabled');
            }
            
            this.renderWidgetResults('');
        } catch (error) {
            console.error('Failed to load master toys:', error);
            UiHelper.showError('Failed to load master toys');
        }
    },

    /**
     * Load master toy items
     * @param {number} masterToyId - Master toy ID
     */
    async loadMasterToyItems(masterToyId) {
        try {
            this.availableMasterToyItems = await CollectionApi.getMasterToyItems(masterToyId);
            this.refreshExistingRows();
            
            // Update dataset for new rows
            const container = document.getElementById('childItemsContainer');
            if (container) {
                container.dataset.masterToyItems = JSON.stringify(this.availableMasterToyItems);
            }
        } catch (error) {
            console.error('Failed to load master toy items:', error);
            UiHelper.showError('Failed to load toy items');
        }
    },

    /**
     * Update widget display
     * @param {Object|null} toy - Selected toy data
     */
    updateWidgetDisplay(toy) {
        const iconEl = document.getElementById('displayToyImgIcon');
        const imgEl = document.getElementById('displayToyImg');

        document.getElementById('displayToyTitle').textContent = toy ? toy.name : 'Select Toy...';

        if (toy) {
            // Meta information
            const line2 = [toy.release_year, toy.type_name].filter(Boolean).join(' - ');
            document.getElementById('displayToyMeta1').textContent = line2;

            const sourceText = toy.source_material_name || (toy.wave_number ? `Wave: ${toy.wave_number}` : '');
            document.getElementById('displayToyMeta2').textContent = sourceText;

            // Image
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
    },

    /**
     * Render widget search results
     * @param {string} filterText - Search term
     */
    renderWidgetResults(filterText = '') {
        const widgetList = document.getElementById('toyResultsList');
        
        if (!widgetList) return;

        widgetList.innerHTML = '';
        const term = filterText.toLowerCase();

        const filtered = this.currentMasterToysList.filter(t => 
            t.name.toLowerCase().includes(term)
        );

        if (filtered.length === 0) {
            widgetList.innerHTML = `
                <div class="p-3 text-center text-muted small">
                    No toys found matching "${UiHelper.escapeHtml(filterText)}"
                </div>
            `;
            return;
        }

        filtered.forEach(toy => {
            const div = document.createElement('div');
            div.className = 'toy-result-item';

            // Build meta data
            const metaParts = [];
            if (toy.release_year) metaParts.push(toy.release_year);
            if (toy.type_name) metaParts.push(toy.type_name);
            if (toy.source_material_name) metaParts.push(toy.source_material_name);

            // Image or icon
            let imgHtml = '<i class="fas fa-robot text-muted"></i>';
            if (toy.image_path) {
                imgHtml = `<img src="${toy.image_path}" class="toy-thumb-img" alt="${UiHelper.escapeHtml(toy.name)}">`;
            }

            div.innerHTML = `
                <div class="toy-thumb-container">${imgHtml}</div>
                <div class="flex-grow-1">
                    <div class="toy-title">${UiHelper.escapeHtml(toy.name)}</div>
                    <div class="text-muted small">${metaParts.join(' &bull; ')}</div>
                </div>
            `;

            div.addEventListener('click', () => {
                const widgetInput = document.getElementById('inputMasterToyId');
                const widgetOverlay = document.getElementById('masterToyOverlay');
                
                if (widgetInput) widgetInput.value = toy.id;
                this.updateWidgetDisplay(toy);
                if (widgetOverlay) widgetOverlay.classList.remove('show');
                this.loadMasterToyItems(toy.id);
            });

            widgetList.appendChild(div);
        });
    },

    /**
     * Add item row
     * @param {Object|null} data - Pre-populated data
     */
    async addItemRow(data = null) {
        const container = document.getElementById('childItemsContainer');
        const template = document.getElementById('childRowTemplate');
        
        if (!container || !template) return;

        // Load master toy items if not already loaded
        if (this.availableMasterToyItems.length === 0) {
            const widgetInput = document.getElementById('inputMasterToyId');
            if (widgetInput && widgetInput.value) {
                await this.loadMasterToyItems(widgetInput.value);
            }
        }

        // Clone template
        const index = this.rowCount++;
        const clone = template.content.cloneNode(true);

        // Update IDs and names
        clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
            el.name = el.name.replace('INDEX', index);
            if (el.id) el.id = el.id.replace('INDEX', index);
        });

        clone.querySelectorAll('[data-bs-target*="INDEX"]').forEach(el => {
            el.setAttribute('data-bs-target', el.getAttribute('data-bs-target').replace('INDEX', index));
            el.setAttribute('aria-controls', el.getAttribute('aria-controls').replace('INDEX', index));
        });

        clone.querySelectorAll('[id*="INDEX"]').forEach(el => {
            el.id = el.id.replace('INDEX', index);
        });

        clone.querySelectorAll('[for*="INDEX"]').forEach(el => {
            el.setAttribute('for', el.getAttribute('for').replace('INDEX', index));
        });

        // Populate master item dropdown
        const masterToyItemSelect = clone.querySelector('.master-toy-item-select');
        
        if (this.availableMasterToyItems.length > 0) {
            let options = '<option value="">Select Item...</option>';
            this.availableMasterToyItems.forEach(mti => {
                options += `<option value="${mti.id}">${UiHelper.escapeHtml(mti.name)} (${UiHelper.escapeHtml(mti.type)})</option>`;
            });
            masterToyItemSelect.innerHTML = options;
        } else {
            masterToyItemSelect.innerHTML = '<option value="">Select Master Toy first</option>';
        }

        // Pre-populate if data provided
        if (data) {
            if (data.master_toy_item_id) {
                masterToyItemSelect.value = data.master_toy_item_id;
            }
            
            const conditionSelect = clone.querySelector('[name*="condition"]');
            if (conditionSelect && data.condition) {
                conditionSelect.value = data.condition;
            }

            const looseCheckbox = clone.querySelector('[name*="is_loose"]');
            if (looseCheckbox && data.is_loose !== undefined) {
                looseCheckbox.checked = data.is_loose == 1;
            }

            const quantityInput = clone.querySelector('[name*="quantity"]');
            if (quantityInput && data.quantity) {
                quantityInput.value = data.quantity;
            }
        }

        // Attach delete handler
        const deleteBtn = clone.querySelector('.btn-delete-row');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const row = this.closest('.child-item-row');
                if (row) {
                    UiHelper.fadeOut(row, 300);
                    setTimeout(() => row.remove(), 300);
                }
            });
        }

        container.appendChild(clone);

        // Scroll to new row
        const newRow = container.lastElementChild;
        if (newRow) {
            UiHelper.scrollTo(newRow, 100);
        }
    },

    /**
     * Add all items from master toy definition
     */
    addAllItemsFromMaster() {
        const container = document.getElementById('childItemsContainer');
        
        if (!container) return;

        let masterItems = [];
        try {
            masterItems = JSON.parse(container.dataset.masterToyItems || '[]');
        } catch (e) {
            console.error('Could not parse master toy items:', e);
            return;
        }

        if (masterItems.length === 0) {
            UiHelper.showWarning('No items defined for this Master Toy in the catalog.');
            return;
        }

        if (masterItems.length > 10) {
            UiHelper.confirm(
                `Add all ${masterItems.length} items?`,
                'Confirm Add All'
            ).then(confirmed => {
                if (confirmed) {
                    this.addAllItems(masterItems);
                }
            });
        } else {
            this.addAllItems(masterItems);
        }
    },

    /**
     * Add all items helper
     * @param {Array} masterItems - Master items to add
     */
    addAllItems(masterItems) {
        masterItems.forEach(masterItem => {
            const itemData = {
                master_toy_item_id: masterItem.id,
                condition: '',
                is_loose: 1,
                quantity: 1
            };

            this.addItemRow(itemData);
        });

        const container = document.getElementById('childItemsContainer');
        const lastRow = container ? container.lastElementChild : null;
        if (lastRow) {
            UiHelper.scrollTo(lastRow, 100);
        }
    },

    /**
     * Refresh existing item rows with new master toy items
     */
    refreshExistingRows() {
        const container = document.getElementById('childItemsContainer');
        
        if (!container) return;

        const selects = container.querySelectorAll('.master-toy-item-select');
        
        selects.forEach(select => {
            const currentVal = select.value;
            let options = this.availableMasterToyItems.length > 0
                ? '<option value="">Select Item...</option>'
                : '<option value="">Select Master Toy first</option>';

            if (this.availableMasterToyItems.length > 0) {
                this.availableMasterToyItems.forEach(mti => {
                    options += `<option value="${mti.id}">${UiHelper.escapeHtml(mti.name)} (${UiHelper.escapeHtml(mti.type)})</option>`;
                });
            }

            select.innerHTML = options;

            if (currentVal && this.availableMasterToyItems.some(p => p.id == currentVal)) {
                select.value = currentVal;
            }
        });
    },

    /**
     * Reset dropdown to default state
     * @param {HTMLElement} element - Select element
     * @param {string} message - Placeholder message
     */
    resetSelect(element, message) {
        if (element) {
            element.innerHTML = `<option value="">${message}</option>`;
            element.disabled = true;
        }
    },

    /**
     * Reset widget to default state
     * @param {string} message - Display message
     */
    resetWidget(message) {
        const widgetCard = document.getElementById('masterToyDisplayCard');
        const widgetInput = document.getElementById('inputMasterToyId');
        
        if (widgetCard) {
            widgetCard.classList.add('disabled');
            document.getElementById('displayToyTitle').textContent = message;
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            
            if (widgetInput) widgetInput.value = '';

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
    },

    /**
     * Populate dropdown with data
     * @param {HTMLElement} element - Select element
     * @param {Array} data - Array of items
     * @param {string} defaultMsg - Default option text
     */
    populateSelect(element, data, defaultMsg) {
        if (!element) return;

        let options = `<option value="">${defaultMsg}</option>`;
        data.forEach(item => {
            options += `<option value="${item.id}">${UiHelper.escapeHtml(item.name)}</option>`;
        });

        element.innerHTML = options;
        element.disabled = false;
    },

    /**
     * Handle successful save
     * @param {Object} data - Response data
     */
    handleSaveSuccess(data) {
        // Close modal
        const modalEl = document.getElementById('appModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Cleanup backdrop
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(bd => bd.remove());
                document.body.classList.remove('modal-open');
            }, 100);
        }

        UiHelper.showSuccess('Toy saved successfully!');

        // Refresh grid
        if (window.CollectionManager && typeof CollectionManager.refreshAfterSave === 'function') {
            CollectionManager.refreshAfterSave(data.id);
        } else {
            setTimeout(() => window.location.reload(), 300);
        }
    },

    /**
     * Open add modal
     */
    openAddModal() {
        if (window.App && typeof App.openModal === 'function') {
            App.openModal('Collection', 'Toy', 'add');
        }
    },

    /**
     * Open edit modal
     * @param {number} id - Toy ID
     */
    openEditModal(id) {
        if (!id) {
            console.error('Missing ID for edit modal');
            return;
        }

        if (window.App && typeof App.openModal === 'function') {
            App.openModal('Collection', 'Toy', 'edit', { id });
        }
    },

    /**
     * Open media modal
     * @param {number} id - Toy ID
     */
    openMediaModal(id) {
        if (!id) {
            console.error('Missing ID for media modal');
            return;
        }

        if (window.App && typeof App.openModal === 'function') {
            App.openModal('Collection', 'Toy', 'media_step', { id });
        }
    }
};

// Make globally available
window.CollectionForms = CollectionForms;

// Legacy compatibility
window.CollectionForm = {
    openAddModal: () => CollectionForms.openAddModal(),
    openEditModal: (id) => CollectionForms.openEditModal(id),
    openMediaModal: (id) => CollectionForms.openMediaModal(id),
    handleSaveSuccess: (data) => CollectionForms.handleSaveSuccess(data),
    addAllItemsFromMaster: () => CollectionForms.addAllItemsFromMaster()
};
