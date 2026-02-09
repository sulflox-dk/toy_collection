/**
 * EntityManager - Base class for managing CRUD operations on entities
 * Provides common functionality for all entity managers
 */
class EntityManager {
	/**
	 * @param {string} entityName - Name of the entity (singular, e.g., 'toy', 'manufacturer')
	 * @param {Object} config - Configuration object
	 * @param {string} config.module - Module name (e.g., 'Collection', 'Catalog')
	 * @param {string} config.controller - Controller name (e.g., 'Toy', 'Manufacturer')
	 * @param {Object} [config.ui] - UI element selectors
	 * @param {string} [config.ui.grid] - Grid container selector
	 * @param {string} [config.ui.modal] - Modal selector
	 * @param {string} [config.ui.form] - Form selector
	 * @param {Object} [config.options] - Additional options
	 */
	constructor(entityName, config) {
		this.entityName = entityName;
		this.entityNamePlural = config.entityNamePlural || `${entityName}s`;
		this.config = config;

		// UI selectors with defaults
		this.ui = {
			grid: config.ui?.grid || `#${entityName}Grid`,
			modal: config.ui?.modal || `#${entityName}Modal`,
			form: config.ui?.form || `#${entityName}Form`,
			...config.ui,
		};

		// Data storage
		this.data = [];
		this.currentEntity = null;

		// State
		this.isLoading = false;
		this.filters = {};

		// Options
		this.options = {
			confirmDelete: true,
			liveValidation: true,
			autoRefresh: true,
			...config.options,
		};
	}

	/**
	 * Initialize the manager
	 * Sets up event handlers and loads initial data
	 */
	init() {
		this.attachEventHandlers();
		this.load();

		if (this.options.liveValidation) {
			this.initFormValidation();
		}
	}

	/**
	 * Attach event handlers
	 * Override in child classes to add specific handlers
	 */
	attachEventHandlers() {
		// New entity button
		const newBtn = document.getElementById(
			`btnNew${this.capitalize(this.entityName)}`,
		);
		if (newBtn) {
			newBtn.addEventListener('click', () => this.showCreateForm());
		}

		// Save button
		const saveBtn = document.getElementById(
			`btnSave${this.capitalize(this.entityName)}`,
		);
		if (saveBtn) {
			saveBtn.addEventListener('click', () => this.handleSave());
		}

		// Form submit
		const form = document.querySelector(this.ui.form);
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				this.handleSave();
			});
		}

		// Search/filter
		const searchInput = document.getElementById(`${this.entityName}Search`);
		if (searchInput) {
			const debouncedSearch = UiHelper.debounce((value) => {
				this.filters.search = value;
				this.load();
			}, 300);

			searchInput.addEventListener('input', (e) => {
				debouncedSearch(e.target.value);
			});
		}
	}

	/**
	 * Initialize form validation
	 */
	initFormValidation() {
		const form = document.querySelector(this.ui.form);
		if (form) {
			Validation.addLiveValidation(form);
		}
	}

	/**
	 * Build API URL for an action
	 * @param {string} action - Controller action
	 * @returns {string} Complete URL
	 */
	buildUrl(action) {
		return ApiClient.buildModuleUrl(
			this.config.module,
			this.config.controller,
			action,
		);
	}

	/**
	 * Make API request
	 * @param {string} action - Controller action
	 * @param {Object} data - Request data
	 * @param {string} method - HTTP method
	 * @returns {Promise<Object>} Response data
	 */
	async request(action, data = {}, method = 'GET') {
		try {
			const url = this.buildUrl(action);

			if (method === 'GET') {
				return await ApiClient.get(url, data);
			} else {
				return await ApiClient.post(url, data);
			}
		} catch (error) {
			this.handleRequestError(error, action);
			throw error;
		}
	}

	/**
	 * Handle API request errors
	 * @param {Error} error - Error object
	 * @param {string} action - Action that failed
	 */
	handleRequestError(error, action) {
		if (error instanceof ApiError) {
			if (error.isValidationError()) {
				UiHelper.showValidationErrors(error.data.errors);
			} else if (error.isNotFound()) {
				UiHelper.showError(`${this.capitalize(this.entityName)} not found`);
			} else if (error.isServerError()) {
				UiHelper.showError('Server error occurred. Please try again.');
			} else {
				UiHelper.showError(error.message);
			}
		} else {
			UiHelper.showError(`Failed to ${action} ${this.entityName}`);
		}
	}

	/**
	 * Load all entities
	 * @param {Object} filters - Filter criteria
	 * @returns {Promise<Array>} Array of entities
	 */
	async load(filters = {}) {
		if (this.isLoading) return;

		this.isLoading = true;
		this.filters = { ...this.filters, ...filters };

		try {
			UiHelper.showLoader(
				this.ui.grid,
				'md',
				`Loading ${this.entityNamePlural}...`,
			);

			const response = await this.request('getAll', this.filters);

			if (response.success) {
				this.data = response.data;
				this.afterLoad(this.data);
				this.renderGrid(this.data);
			} else {
				throw new Error(response.message || 'Failed to load data');
			}
		} catch (error) {
			console.error(`Failed to load ${this.entityNamePlural}:`, error);
			this.renderError('Failed to load data. Please try again.');
		} finally {
			UiHelper.hideLoader(this.ui.grid);
			this.isLoading = false;
		}
	}

	/**
	 * Get entity by ID
	 * @param {number} id - Entity ID
	 * @returns {Promise<Object>} Entity data
	 */
	async getById(id) {
		try {
			const response = await this.request('getById', { id });

			if (response.success) {
				return response.data;
			} else {
				throw new Error(response.message || 'Failed to get entity');
			}
		} catch (error) {
			console.error(`Failed to get ${this.entityName}:`, error);
			throw error;
		}
	}

	/**
	 * Create new entity
	 * @param {Object} data - Entity data
	 * @returns {Promise<Object>} Created entity
	 */
	async create(data) {
		try {
			// Validate before saving
			if (!this.validateData(data)) {
				return;
			}

			// Allow modification before saving
			data = this.beforeSave(data);

			UiHelper.showLoader(this.ui.modal, 'sm', 'Creating...');

			const response = await this.request('create', data, 'POST');

			if (response.success) {
				this.afterSave(response.data, 'create');
				UiHelper.showSuccess(
					`${this.capitalize(this.entityName)} created successfully`,
				);

				if (this.options.autoRefresh) {
					this.load();
				}

				this.closeModal();
				return response.data;
			} else {
				throw new Error(response.message || 'Failed to create entity');
			}
		} catch (error) {
			console.error(`Failed to create ${this.entityName}:`, error);
			throw error;
		} finally {
			UiHelper.hideLoader(this.ui.modal);
		}
	}

	/**
	 * Update existing entity
	 * @param {number} id - Entity ID
	 * @param {Object} data - Entity data
	 * @returns {Promise<Object>} Updated entity
	 */
	async update(id, data) {
		try {
			// Validate before saving
			if (!this.validateData(data)) {
				return;
			}

			// Allow modification before saving
			data = this.beforeSave(data);

			UiHelper.showLoader(this.ui.modal, 'sm', 'Updating...');

			const response = await this.request('update', { id, ...data }, 'POST');

			if (response.success) {
				this.afterSave(response.data, 'update');
				UiHelper.showSuccess(
					`${this.capitalize(this.entityName)} updated successfully`,
				);

				if (this.options.autoRefresh) {
					this.load();
				}

				this.closeModal();
				return response.data;
			} else {
				throw new Error(response.message || 'Failed to update entity');
			}
		} catch (error) {
			console.error(`Failed to update ${this.entityName}:`, error);
			throw error;
		} finally {
			UiHelper.hideLoader(this.ui.modal);
		}
	}

	/**
	 * Delete entity
	 * @param {number} id - Entity ID
	 * @param {string} name - Entity name for confirmation
	 * @returns {Promise<boolean>} True if deleted
	 */
	async delete(id, name = null) {
		try {
			// Confirm deletion
			if (this.options.confirmDelete) {
				const entityName = name || `this ${this.entityName}`;
				const confirmed = await UiHelper.confirmDelete(entityName);

				if (!confirmed) {
					return false;
				}
			}

			// Allow custom logic before delete
			this.beforeDelete(id);

			const response = await this.request('delete', { id }, 'POST');

			if (response.success) {
				this.afterDelete(id);
				UiHelper.showSuccess(
					`${this.capitalize(this.entityName)} deleted successfully`,
				);

				if (this.options.autoRefresh) {
					this.load();
				}

				return true;
			} else {
				throw new Error(response.message || 'Failed to delete entity');
			}
		} catch (error) {
			console.error(`Failed to delete ${this.entityName}:`, error);
			return false;
		}
	}

	/**
	 * Show create form
	 */
	showCreateForm() {
		this.currentEntity = null;
		this.resetForm();
		this.updateModalTitle('create');
		this.beforeShowForm('create', null);
		this.showModal();
	}

	/**
	 * Show edit form
	 * @param {number} id - Entity ID
	 */
	async showEditForm(id) {
		try {
			UiHelper.showLoader(this.ui.modal, 'sm', 'Loading...');
			this.showModal();

			const entity = await this.getById(id);
			this.currentEntity = entity;

			this.populateForm(entity);
			this.updateModalTitle('edit');
			this.beforeShowForm('edit', entity);

			UiHelper.hideLoader(this.ui.modal);
		} catch (error) {
			UiHelper.showError('Failed to load entity details');
			this.closeModal();
		}
	}

	/**
	 * Handle save button click
	 */
	async handleSave() {
		const form = document.querySelector(this.ui.form);

		if (!form) {
			console.error('Form not found');
			return;
		}

		// Validate form
		if (!Validation.validateForm(form)) {
			return;
		}

		// Get form data
		const formData = new FormData(form);
		const data = Object.fromEntries(formData);

		try {
			if (this.currentEntity && this.currentEntity.id) {
				await this.update(this.currentEntity.id, data);
			} else {
				await this.create(data);
			}
		} catch (error) {
			// Error already handled in create/update methods
			console.error('Save failed:', error);
		}
	}

	/**
	 * Populate form with entity data
	 * @param {Object} entity - Entity data
	 */
	populateForm(entity) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		Object.keys(entity).forEach((key) => {
			const field = form.querySelector(`[name="${key}"]`);
			if (!field) return;

			if (field.type === 'checkbox') {
				field.checked = entity[key] == 1 || entity[key] === true;
			} else if (field.type === 'radio') {
				const radio = form.querySelector(
					`[name="${key}"][value="${entity[key]}"]`,
				);
				if (radio) radio.checked = true;
			} else {
				field.value = entity[key] || '';
			}
		});
	}

	/**
	 * Reset form to initial state
	 */
	resetForm() {
		const form = document.querySelector(this.ui.form);
		if (form) {
			form.reset();
			Validation.clearValidation(form);
		}
	}

	/**
	 * Update modal title
	 * @param {string} mode - 'create' or 'edit'
	 */
	updateModalTitle(mode) {
		const modalTitle = document.querySelector(
			`${this.ui.modal} .modal-title`,
		);
		if (modalTitle) {
			const title =
				mode === 'create'
					? `New ${this.capitalize(this.entityName)}`
					: `Edit ${this.capitalize(this.entityName)}`;
			modalTitle.textContent = title;
		}
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
			if (modal) {
				modal.hide();
			}
		}
	}

	/**
	 * Render grid with entities
	 * @param {Array} entities - Array of entities
	 */
	renderGrid(entities) {
		const grid = document.querySelector(this.ui.grid);
		if (!grid) return;

		grid.innerHTML = '';

		if (entities.length === 0) {
			this.renderEmpty();
			return;
		}

		entities.forEach((entity) => {
			const card = this.createCard(entity);
			grid.appendChild(card);
		});
	}

	/**
	 * Create card element for entity
	 * Override in child classes for custom rendering
	 * @param {Object} entity - Entity data
	 * @returns {HTMLElement} Card element
	 */
	createCard(entity) {
		const col = document.createElement('div');
		col.className = 'col-md-4 col-lg-3 mb-3';

		col.innerHTML = `
            <div class="card ${this.entityName}-card h-100">
                <div class="card-body">
                    <h5 class="card-title">${this.escapeHtml(entity.name || 'Unnamed')}</h5>
                    ${this.renderCardContent(entity)}
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-sm btn-primary flex-fill" 
                                onclick="${this.getInstanceName()}.showEditForm(${entity.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                onclick="${this.getInstanceName()}.delete(${entity.id}, '${this.escapeHtml(entity.name || '')}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

		return col;
	}

	/**
	 * Render card content
	 * Override in child classes to customize
	 * @param {Object} entity - Entity data
	 * @returns {string} HTML string
	 */
	renderCardContent(entity) {
		return '';
	}

	/**
	 * Render empty state
	 */
	renderEmpty() {
		const grid = document.querySelector(this.ui.grid);
		if (!grid) return;

		grid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No ${this.entityNamePlural} found.
                </div>
            </div>
        `;
	}

	/**
	 * Render error state
	 * @param {string} message - Error message
	 */
	renderError(message) {
		const grid = document.querySelector(this.ui.grid);
		if (!grid) return;

		grid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    ${this.escapeHtml(message)}
                </div>
            </div>
        `;
	}

	/**
	 * Get instance name for global access
	 * @returns {string} Instance variable name
	 */
	getInstanceName() {
		return `${this.entityName}Manager`;
	}

	/**
	 * Escape HTML to prevent XSS
	 * @param {string} text - Text to escape
	 * @returns {string} Escaped text
	 */
	escapeHtml(text) {
		if (text === null || text === undefined) return '';
		const div = document.createElement('div');
		div.textContent = text.toString();
		return div.innerHTML;
	}

	/**
	 * Capitalize first letter
	 * @param {string} str - String to capitalize
	 * @returns {string} Capitalized string
	 */
	capitalize(str) {
		return str.charAt(0).toUpperCase() + str.slice(1);
	}

	// ========================================
	// Lifecycle Hooks
	// Override these in child classes
	// ========================================

	/**
	 * Validate entity data
	 * @param {Object} data - Data to validate
	 * @returns {boolean} True if valid
	 */
	validateData(data) {
		// Custom validation logic in child classes
		return true;
	}

	/**
	 * Called before saving (create or update)
	 * @param {Object} data - Data to be saved
	 * @returns {Object} Modified data
	 */
	beforeSave(data) {
		return data;
	}

	/**
	 * Called after successful save
	 * @param {Object} entity - Saved entity
	 * @param {string} mode - 'create' or 'update'
	 */
	afterSave(entity, mode) {
		// Custom logic after save
	}

	/**
	 * Called after data is loaded
	 * @param {Array} data - Loaded entities
	 */
	afterLoad(data) {
		// Custom logic after load
	}

	/**
	 * Called before showing form
	 * @param {string} mode - 'create' or 'edit'
	 * @param {Object|null} entity - Entity data (null for create)
	 */
	beforeShowForm(mode, entity) {
		// Custom logic before showing form
	}

	/**
	 * Called before delete
	 * @param {number} id - Entity ID to be deleted
	 */
	beforeDelete(id) {
		// Custom logic before delete
	}

	/**
	 * Called after successful delete
	 * @param {number} id - Deleted entity ID
	 */
	afterDelete(id) {
		// Custom logic after delete
	}
}

// Make globally available
window.EntityManager = EntityManager;
