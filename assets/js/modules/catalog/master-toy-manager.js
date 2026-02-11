/**
 * Master Toy Manager
 *
 * Provides master toy data access for cascading dropdowns.
 * Used by collection-forms.js for the add/edit toy modal.
 *
 * This is a lightweight manager focused on data retrieval.
 * The full CRUD management page uses MasterToyMgr in master_toy_index.
 */
class MasterToyManager {
	constructor() {
		this.baseUrl =
			typeof App !== 'undefined' && App.baseUrl ? App.baseUrl : '/';

		// Cache for line-filtered master toys
		this.lineCache = new Map();
	}

	/**
	 * Get master toys filtered by toy line
	 * Used by collection-forms.js cascading dropdowns
	 *
	 * @param {number} lineId - Toy Line ID to filter by
	 * @returns {Promise<Array>} Array of master toy objects with full details
	 */
	async getByLine(lineId) {
		if (!lineId) {
			return [];
		}

		// Check cache first
		const cacheKey = `line_${lineId}`;
		if (this.lineCache.has(cacheKey)) {
			console.log(
				'MasterToyManager: Returning cached data for line',
				lineId,
			);
			return this.lineCache.get(cacheKey);
		}

		try {
			// Uses CatalogModel::getMasterToysByLine via a new endpoint
			const url = `${this.baseUrl}?module=Catalog&controller=MasterToy&action=get_json&line_id=${lineId}`;

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();

			// Cache the result
			this.lineCache.set(cacheKey, data);

			return data;
		} catch (error) {
			console.error('MasterToyManager: getByLine failed', error);
			if (typeof UiHelper !== 'undefined') {
				UiHelper.showError('Failed to load master toys');
			}
			return [];
		}
	}

	/**
	 * Get master toy by ID with full details
	 * @param {number} id - Master Toy ID
	 * @returns {Promise<Object|null>} Master toy object
	 */
	async getById(id) {
		if (!id) {
			return null;
		}

		try {
			const url = `${this.baseUrl}?module=Catalog&controller=MasterToy&action=get_by_id&id=${id}`;

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();

			if (data.success) {
				return data.data;
			}

			return data;
		} catch (error) {
			console.error('MasterToyManager: getById failed', error);
			return null;
		}
	}

	/**
	 * Get items for a master toy
	 * @param {number} masterToyId - Master Toy ID
	 * @returns {Promise<Array>} Array of item objects
	 */
	async getItems(masterToyId) {
		if (!masterToyId) {
			return [];
		}

		try {
			const url = `${this.baseUrl}?module=Catalog&controller=MasterToy&action=get_items&id=${masterToyId}`;

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();
			return Array.isArray(data) ? data : data.data || [];
		} catch (error) {
			console.error('MasterToyManager: getItems failed', error);
			return [];
		}
	}

	/**
	 * Clear the cache
	 * Call after create/update/delete operations
	 */
	clearCache() {
		this.lineCache.clear();
	}
}

// Create global instance for collection-forms.js
window.masterToyManager = new MasterToyManager();

console.log('MasterToyManager: Ready (global instance created)');
