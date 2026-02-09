/**
 * Collection Manager
 * Main manager class for Collection module - extends EntityManager
 */
class CollectionManager extends EntityManager {
    constructor() {
        super('toy', {
            module: 'Collection',
            controller: 'Toy',
            entityNamePlural: 'toys',
            ui: {
                grid: '#collectionGridContainer',
                modal: '#appModal'
            },
            options: {
                confirmDelete: true,
                liveValidation: false, // Forms handle their own validation
                autoRefresh: false // We handle refresh manually
            }
        });

        // Additional state
        this.filters = {
            universe_id: '',
            line_id: '',
            ent_source_id: '',
            storage_id: '',
            source_id: '',
            status: '',
            manufacturer_id: '',
            product_type_id: '',
            completeness: '',
            missing_parts: '',
            image_status: '',
            search: ''
        };

        // Filter element references
        this.filterElements = {};
    }

    /**
     * Initialize collection manager
     */
    init() {
        console.log('CollectionManager: Initializing...');

        this.container = document.getElementById('collectionGridContainer');

        // Initialize UI module
        if (window.CollectionUi) {
            CollectionUi.init();
        }

        // Initialize Forms module
        if (window.CollectionForms) {
            CollectionForms.init();
        }

        // Initialize Media module if on media page
        if (document.getElementById('media-upload-container') && window.CollectionMedia) {
            CollectionMedia.init();
        }

        // Setup filter elements
        this.setupFilterElements();

        // Attach event handlers
        this.attachEventHandlers();

        // Load initial data if container exists
        if (this.container) {
            this.loadPage(1);
        }

        console.log('CollectionManager: Initialization complete');
    }

    /**
     * Setup filter element references
     */
    setupFilterElements() {
        this.filterElements = {
            search: document.getElementById('searchCollection'),
            universe: document.getElementById('filterUniverse'),
            line: document.getElementById('filterLine'),
            entSource: document.getElementById('filterEntSource') || document.getElementById('filterSource'),
            storage: document.getElementById('filterStorage'),
            source: document.getElementById('filterPurchaseSource'),
            status: document.getElementById('filterStatus'),
            manufacturer: document.getElementById('filterManufacturer'),
            productType: document.getElementById('filterProductType'),
            completeness: document.getElementById('filterCompleteness'),
            missingParts: document.getElementById('filterMissingParts'),
            imageStatus: document.getElementById('filterImage')
        };
    }

    /**
     * Attach event handlers
     */
    attachEventHandlers() {
        // Filter change handlers
        Object.values(this.filterElements).forEach(el => {
            if (el) {
                el.addEventListener('change', () => this.loadPage(1));
            }
        });

        // Search with debounce
        if (this.filterElements.search) {
            const debouncedSearch = UiHelper.debounce(() => this.loadPage(1), 400);
            this.filterElements.search.addEventListener('keyup', debouncedSearch);
        }

        // Reset filters button
        const resetBtn = document.getElementById('btnResetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetFilters());
        }

        // Global click handler for buttons
        document.body.addEventListener('click', (e) => {
            this.handleGlobalClick(e);
        });
    }

    /**
     * Handle global click events
     * @param {Event} e - Click event
     */
    handleGlobalClick(e) {
        // Helper to find data-id
        const findId = (el) => {
            const container = el.closest('[data-id]') || el.closest('tr');
            return container ? container.dataset.id : null;
        };

        // Delete button
        const delBtn = e.target.closest('.btn-delete');
        if (delBtn) {
            e.preventDefault();
            this.handleDelete(delBtn);
            return;
        }

        // Edit button
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            e.preventDefault();
            const id = findId(editBtn);
            if (id && window.CollectionForms) {
                CollectionForms.openEditModal(id);
            }
            return;
        }

        // Media button
        const mediaBtn = e.target.closest('.btn-media');
        if (mediaBtn) {
            e.preventDefault();
            const id = findId(mediaBtn);
            if (id && window.CollectionForms) {
                CollectionForms.openMediaModal(id);
            }
            return;
        }
    }

    /**
     * Handle delete action
     * @param {HTMLElement} btn - Delete button
     */
    async handleDelete(btn) {
        const container = btn.closest('[data-id]') || btn.closest('tr');
        const id = container ? container.dataset.id : null;

        if (!id) return;

        // Confirm deletion
        const confirmed = await UiHelper.confirm(
            'Are you sure? This will delete the toy and all associated images permanently.',
            'Confirm Delete',
            { danger: true, confirmText: 'Delete', cancelText: 'Cancel' }
        );

        if (!confirmed) return;

        // Show loading state
        container.style.opacity = '0.3';

        try {
            const response = await CollectionApi.delete(id);

            if (response.success) {
                UiHelper.showSuccess('Toy deleted successfully');

                // Reload grid
                if (this.container) {
                    this.loadPage(this.currentPage);
                } else {
                    window.location.reload();
                }
            } else {
                container.style.opacity = '1';
                UiHelper.showError(response.error || 'Failed to delete toy');
            }
        } catch (error) {
            container.style.opacity = '1';
            console.error('Delete failed:', error);
            UiHelper.showError('Failed to delete toy');
        }
    }

    /**
     * Delete collection item (child item)
     * @param {number} itemId - Item ID
     * @param {HTMLElement} btnElement - Button element
     */
    async deleteToyItem(itemId, btnElement) {
        const confirmed = await UiHelper.confirmDelete('this item from your collection');
        
        if (!confirmed) return;

        try {
            const response = await CollectionApi.deleteItem(itemId);

            if (response.success) {
                const row = btnElement.closest('.child-item-row');

                if (row) {
                    // Deletion inside modal
                    UiHelper.fadeOut(row, 300);
                    setTimeout(() => row.remove(), 300);
                } else {
                    // Deletion from dashboard/grid
                    const card = document.querySelector(`[data-id="${itemId}"]`);
                    if (card) {
                        UiHelper.fadeOut(card, 300);
                        setTimeout(() => {
                            this.loadPage(this.currentPage || 1);
                        }, 300);
                    } else {
                        window.location.reload();
                    }
                }

                UiHelper.showSuccess('Item deleted successfully');
            } else {
                UiHelper.showError(response.error || 'Failed to delete item');
            }
        } catch (error) {
            console.error('Delete item failed:', error);
            UiHelper.showError('Failed to delete item');
        }
    }

    /**
     * Reset all filters
     */
    resetFilters() {
        // Reset filter elements
        Object.values(this.filterElements).forEach(el => {
            if (el) {
                el.value = '';
            }
        });

        // Reset filters object
        this.filters = {
            universe_id: '',
            line_id: '',
            ent_source_id: '',
            storage_id: '',
            source_id: '',
            status: '',
            manufacturer_id: '',
            product_type_id: '',
            completeness: '',
            missing_parts: '',
            image_status: '',
            search: ''
        };

        // Reload
        this.loadPage(1);
    }

    /**
     * Build filter parameters from current state
     * @returns {Object} Filter parameters
     */
    buildFilterParams() {
        return {
            universe_id: this.filterElements.universe?.value || '',
            line_id: this.filterElements.line?.value || '',
            ent_source_id: this.filterElements.entSource?.value || '',
            storage_id: this.filterElements.storage?.value || '',
            source_id: this.filterElements.source?.value || '',
            status: this.filterElements.status?.value || '',
            manufacturer_id: this.filterElements.manufacturer?.value || '',
            product_type_id: this.filterElements.productType?.value || '',
            completeness: this.filterElements.completeness?.value || '',
            missing_parts: this.filterElements.missingParts?.value || '',
            image_status: this.filterElements.imageStatus?.value || '',
            search: this.filterElements.search?.value || ''
        };
    }

    /**
     * Load page with filters
     * @param {number} page - Page number
     */
    async loadPage(page) {
        this.currentPage = page;

        if (!this.container) return;

        try {
            // Show loading state
            CollectionUi.showLoading();

            // Build filter parameters
            const filters = this.buildFilterParams();

            // Fetch data (now returns JSON object)
            const data = await CollectionApi.getAll(filters, page);

            // Render grid with data
            CollectionUi.renderGrid(data);
        } catch (error) {
            console.error('Load page failed:', error);
            CollectionUi.renderError('Failed to load toys. Please try again.');
        } finally {
            CollectionUi.hideLoading();
        }
    }

    /**
     * Refresh single item
     * @param {number} id - Item ID
     */
    async refreshItem(id) {
        console.log('CollectionManager: Refreshing item', id);

        try {
            const data = await CollectionApi.getById(id);
            CollectionUi.refreshItem(id, data);
        } catch (error) {
            console.error('Refresh item failed:', error);
        }
    }

    /**
     * Refresh after successful save
     * @param {number} id - Saved item ID
     */
    refreshAfterSave(id) {
        // Check if item exists in current grid
        const itemExists = this.container 
            ? this.container.querySelector(`[data-id="${id}"]`) !== null
            : document.querySelector(`.toy-card[data-id="${id}"], tr[data-id="${id}"]`) !== null;

        if (itemExists) {
            // Item exists - refresh it
            console.log('Smart Refresh: Updating item', id);
            this.refreshItem(id);
        } else {
            // Item is new or not in current view - reload page
            console.log('Smart Refresh: Item not found, reloading page');
            setTimeout(() => window.location.reload(), 300);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Create instance
    const collectionManagerInstance = new CollectionManager();
    collectionManagerInstance.init();
    
    // Make instance globally available
    window.CollectionManager = collectionManagerInstance;
    
    // Legacy compatibility
    window.CollectionMgr = collectionManagerInstance;
    window.App = window.App || {};
    App.deleteToyItem = (itemId, btnElement) => collectionManagerInstance.deleteToyItem(itemId, btnElement);
    App.initDependentDropdowns = () => {
        if (window.CollectionForms) CollectionForms.init();
    };
});
