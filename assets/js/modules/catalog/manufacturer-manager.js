/**
 * Manufacturer Manager
 * Extends EntityManager for manufacturer-specific operations
 *
 * Used in two contexts:
 * 1. Catalog module - Full CRUD management page
 * 2. Collection module - Cascading dropdown support (getByUniverse)
 */
class ManufacturerManager extends EntityManager {
	constructor() {
		super('manufacturer', {
			module: 'Catalog',
			controller: 'Manufacturer',
			entityNamePlural: 'manufacturers',
			ui: {
				grid: '#manufacturerGrid',
				modal: '#manufacturerModal',
				form: '#manufacturerForm',
			},
			options: {
				confirmDelete: true,
				liveValidation: true,
				autoRefresh: false, // We use server-rendered HTML
			},
		});

		// Cache for universe-filtered manufacturers
		this.universeCache = new Map();
	}

	/**
	 * Initialize the manager
	 * Only called when on the Manufacturers management page
	 */
	init() {
		console.log('ManufacturerManager: Initializing...');

		this.container = document.querySelector(this.ui.grid);

		// Only setup full UI if we're on the management page
		if (this.container) {
			this.attachEventHandlers();
			this.loadPage(1);
		}

		console.log('ManufacturerManager: Ready');
	}

	/**
	 * Attach event handlers for the management page
	 */
	attachEventHandlers() {
		// New manufacturer button
		const newBtn = document.getElementById('btnNewManufacturer');
		if (newBtn) {
			newBtn.addEventListener('click', () => this.showCreateForm());
		}

		// Search with debounce
		const searchInput = document.getElementById('manufacturerSearch');
		if (searchInput) {
			const debouncedSearch = UiHelper.debounce(() => this.loadPage(1), 300);
			searchInput.addEventListener('input', debouncedSearch);
		}

		// Form submit
		const form = document.querySelector(this.ui.form);
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				this.handleSave();
			});
		}

		// Delegate click handlers for edit/delete buttons
		if (this.container) {
			this.container.addEventListener('click', (e) => {
				const editBtn = e.target.closest('.btn-edit');
				const deleteBtn = e.target.closest('.btn-delete');

				if (editBtn) {
					e.preventDefault();
					const row = editBtn.closest('[data-id]');
					if (row) this.showEditForm(row.dataset.id);
				}

				if (deleteBtn) {
					e.preventDefault();
					const row = deleteBtn.closest('[data-id]');
					if (row) this.handleDelete(row);
				}
			});
		}
	}

	/**
	 * Load page with server-rendered HTML (hybrid approach)
	 * @param {number} page - Page number
	 */
	async loadPage(page = 1) {
		if (!this.container) return;

		this.currentPage = page;

		try {
			// Show loading state
			this.container.innerHTML = `
				<div class="text-center p-5">
					<div class="spinner-border text-secondary" role="status"></div>
					<div class="mt-2 text-muted">Loading manufacturers...</div>
				</div>
			`;

			// Build URL with filters
			const searchInput = document.getElementById('manufacturerSearch');
			const params = {
				ajax_grid: 1,
				page: page,
				search: searchInput?.value || '',
			};

			const url = ApiClient.buildModuleUrl(
				this.config.module,
				this.config.controller,
				'index',
				params,
			);

			// Fetch rendered HTML
			const html = await ApiClient.fetchHtml(url);

			// Insert HTML
			this.container.innerHTML = html;
		} catch (error) {
			console.error('ManufacturerManager: Load failed', error);
			this.container.innerHTML = `
				<div class="alert alert-danger m-3">
					Failed to load manufacturers. Please try again.
				</div>
			`;
		}
	}

	/**
	 * Get manufacturers filtered by universe
	 * This is the key method used by Collection module's cascading dropdowns
	 *
	 * @param {number} universeId - Universe ID to filter by
	 * @returns {Promise<Array>} Array of {id, name} objects
	 */
	async getByUniverse(universeId) {
		if (!universeId) {
			return [];
		}

		// Check cache first
		const cacheKey = `universe_${universeId}`;
		if (this.universeCache.has(cacheKey)) {
			console.log(
				'ManufacturerManager: Returning cached data for universe',
				universeId,
			);
			return this.universeCache.get(cacheKey);
		}

		try {
			const url = ApiClient.buildModuleUrl(
				this.config.module,
				this.config.controller,
				'get_json',
				{ universe_id: universeId },
			);

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();

			// Cache the result
			this.universeCache.set(cacheKey, data);

			return data;
		} catch (error) {
			console.error('ManufacturerManager: getByUniverse failed', error);
			UiHelper.showError('Failed to load manufacturers');
			return [];
		}
	}

	/**
	 * Get all manufacturers (simple list)
	 * @returns {Promise<Array>} Array of {id, name} objects
	 */
	async getAllSimple() {
		try {
			const url = ApiClient.buildModuleUrl(
				this.config.module,
				this.config.controller,
				'get_all_simple',
			);

			const response = await ApiClient.get(url);

			if (Array.isArray(response)) {
				return response;
			}

			return response.data || [];
		} catch (error) {
			console.error('ManufacturerManager: getAllSimple failed', error);
			return [];
		}
	}

	/**
	 * Clear the universe cache
	 * Called after create/update/delete operations
	 */
	clearCache() {
		this.universeCache.clear();
	}

	/**
	 * Show create form in modal
	 */
	showCreateForm() {
		this.currentEntity = null;

		const form = document.querySelector(this.ui.form);
		if (form) {
			form.reset();
			Validation.clearValidation(form);
		}

		// Update modal title
		const title = document.querySelector(`${this.ui.modal} .modal-title`);
		if (title) title.textContent = 'New Manufacturer';

		// Clear hidden ID field
		const idField = document.getElementById('manufacturerId');
		if (idField) idField.value = '';

		this.showModal();
	}

	/**
	 * Show edit form in modal
	 * @param {number} id - Manufacturer ID
	 */
	async showEditForm(id) {
		try {
			this.showModal();

			// Get data from row's data attribute (already embedded in HTML)
			const row = document.querySelector(`[data-id="${id}"]`);
			if (!row) {
				throw new Error('Row not found');
			}

			const data = JSON.parse(row.dataset.json || '{}');
			this.currentEntity = data;

			// Populate form
			const form = document.querySelector(this.ui.form);
			if (form) {
				const nameInput = form.querySelector('[name="name"]');
				const showInput = form.querySelector('[name="show_on_dashboard"]');
				const idInput = document.getElementById('manufacturerId');

				if (nameInput) nameInput.value = data.name || '';
				if (showInput) showInput.checked = data.show_on_dashboard == 1;
				if (idInput) idInput.value = data.id;
			}

			// Update modal title
			const title = document.querySelector(`${this.ui.modal} .modal-title`);
			if (title) title.textContent = 'Edit Manufacturer';
		} catch (error) {
			console.error('ManufacturerManager: showEditForm failed', error);
			UiHelper.showError('Failed to load manufacturer details');
			this.closeModal();
		}
	}

	/**
	 * Handle form save (create or update)
	 */
	async handleSave() {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		// Validate
		if (!Validation.validateForm(form)) {
			return;
		}

		const formData = new FormData(form);
		const data = Object.fromEntries(formData);

		// Convert checkbox
		data.show_on_dashboard = form.querySelector('[name="show_on_dashboard"]')
			?.checked
			? 1
			: 0;

		const id = document.getElementById('manufacturerId')?.value;

		try {
			let url;
			if (id) {
				data.id = id;
				url = ApiClient.buildModuleUrl(
					this.config.module,
					this.config.controller,
					'update',
				);
			} else {
				url = ApiClient.buildModuleUrl(
					this.config.module,
					this.config.controller,
					'store',
				);
			}

			const response = await ApiClient.post(url, data);

			if (response.success) {
				UiHelper.showSuccess(
					id ? 'Manufacturer updated' : 'Manufacturer created',
				);
				this.closeModal();
				this.clearCache();
				this.loadPage(this.currentPage || 1);
			} else {
				throw new Error(response.error || 'Save failed');
			}
		} catch (error) {
			console.error('ManufacturerManager: Save failed', error);
			UiHelper.showError(error.message || 'Failed to save manufacturer');
		}
	}

	/**
	 * Handle delete action
	 * @param {HTMLElement} row - Table row element
	 */
	async handleDelete(row) {
		const id = row.dataset.id;
		const data = JSON.parse(row.dataset.json || '{}');
		const name = data.name || 'this manufacturer';

		const confirmed = await UiHelper.confirmDelete(name);
		if (!confirmed) return;

		try {
			row.style.opacity = '0.5';

			const url = ApiClient.buildModuleUrl(
				this.config.module,
				this.config.controller,
				'delete',
			);

			const response = await ApiClient.post(url, { id });

			if (response.success) {
				UiHelper.showSuccess('Manufacturer deleted');
				this.clearCache();
				this.loadPage(this.currentPage || 1);
			} else {
				throw new Error(response.error || 'Delete failed');
			}
		} catch (error) {
			console.error('ManufacturerManager: Delete failed', error);
			row.style.opacity = '1';
			UiHelper.showError(error.message || 'Failed to delete manufacturer');
		}
	}

	/**
	 * Validate manufacturer data
	 * @param {Object} data - Form data
	 * @returns {boolean} True if valid
	 */
	validateData(data) {
		const errors = [];

		if (!data.name || data.name.trim() === '') {
			errors.push('Manufacturer name is required');
		}

		if (data.name && data.name.length > 100) {
			errors.push('Manufacturer name must not exceed 100 characters');
		}

		if (errors.length > 0) {
			errors.forEach((e) => UiHelper.showError(e));
			return false;
		}

		return true;
	}

	/**
	 * Show modal
	 */
	showModal() {
		const modalElement = document.querySelector(this.ui.modal);
		if (modalElement) {
			const modal =
				bootstrap.Modal.getInstance(modalElement) ||
				new bootstrap.Modal(modalElement);
			modal.show();
		}
	}

	/**
	 * Close modal
	 */
	closeModal() {
		const modalElement = document.querySelector(this.ui.modal);
		if (modalElement) {
			const modal = bootstrap.Modal.getInstance(modalElement);
			if (modal) modal.hide();
		}
	}
}

// Create global instance
window.manufacturerManager = new ManufacturerManager();

// Legacy alias for existing code that might use ManMgr
window.ManMgr = {
	loadPage: (page) => window.manufacturerManager.loadPage(page),
};

// Auto-initialize if we're on the manufacturers page
document.addEventListener('DOMContentLoaded', () => {
	// Only auto-init if the grid container exists (we're on the management page)
	if (document.getElementById('manufacturerGrid')) {
		window.manufacturerManager.init();
	}
});
