/**
 * Manufacturer Manager
 *
 * Provides manufacturer data access for cascading dropdowns.
 * Used by collection-forms.js for the add/edit toy modal.
 *
 * This is a lightweight manager focused on data retrieval.
 * The full CRUD management page uses ManMgr in manufacturers_index.
 */
class ManufacturerManager {
	constructor() {
		this.baseUrl =
			typeof App !== 'undefined' && App.baseUrl ? App.baseUrl : '/';

		// Cache for universe-filtered manufacturers
		this.universeCache = new Map();
	}

	/**
	 * Get manufacturers filtered by universe
	 * Used by collection-forms.js cascading dropdowns
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
			const url = `${this.baseUrl}?module=Catalog&controller=Manufacturer&action=get_json&universe_id=${universeId}`;

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
			if (typeof UiHelper !== 'undefined') {
				UiHelper.showError('Failed to load manufacturers');
			}
			return [];
		}
	}

	/**
	 * Get all manufacturers (simple list)
	 * @returns {Promise<Array>} Array of {id, name} objects
	 */
	async getAllSimple() {
		try {
			const url = `${this.baseUrl}?module=Catalog&controller=Manufacturer&action=get_all_simple`;

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			return await response.json();
		} catch (error) {
			console.error('ManufacturerManager: getAllSimple failed', error);
			return [];
		}
	}

	/**
	 * Clear the cache
	 * Call after create/update/delete operations
	 */
	clearCache() {
		this.universeCache.clear();
	}
}

// Create global instance for collection-forms.js
window.manufacturerManager = new ManufacturerManager();

console.log('ManufacturerManager: Ready (global instance created)');
