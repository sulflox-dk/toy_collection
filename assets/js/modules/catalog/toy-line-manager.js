/**
 * Toy Line Manager
 *
 * Provides toy line data access for cascading dropdowns.
 * Used by collection-forms.js for the add/edit toy modal.
 *
 * This is a lightweight manager focused on data retrieval.
 * The full CRUD management page uses LineMgr in toy_lines_index.
 */
class ToyLineManager {
	constructor() {
		this.baseUrl =
			typeof App !== 'undefined' && App.baseUrl ? App.baseUrl : '/';

		// Cache for manufacturer-filtered lines
		this.manufacturerCache = new Map();
	}

	/**
	 * Get toy lines filtered by manufacturer
	 * Used by collection-forms.js cascading dropdowns
	 *
	 * @param {number} manufacturerId - Manufacturer ID to filter by
	 * @returns {Promise<Array>} Array of {id, name} objects
	 */
	async getByManufacturer(manufacturerId) {
		if (!manufacturerId) {
			return [];
		}

		// Check cache first
		const cacheKey = `manufacturer_${manufacturerId}`;
		if (this.manufacturerCache.has(cacheKey)) {
			console.log(
				'ToyLineManager: Returning cached data for manufacturer',
				manufacturerId,
			);
			return this.manufacturerCache.get(cacheKey);
		}

		try {
			const url = `${this.baseUrl}?module=Catalog&controller=ToyLine&action=get_json&manufacturer_id=${manufacturerId}`;

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const data = await response.json();

			// Cache the result
			this.manufacturerCache.set(cacheKey, data);

			return data;
		} catch (error) {
			console.error('ToyLineManager: getByManufacturer failed', error);
			if (typeof UiHelper !== 'undefined') {
				UiHelper.showError('Failed to load toy lines');
			}
			return [];
		}
	}

	/**
	 * Get all toy lines (simple list)
	 * @returns {Promise<Array>} Array of {id, name} objects
	 */
	async getAllSimple() {
		try {
			const url = `${this.baseUrl}?module=Catalog&controller=ToyLine&action=get_all_simple`;

			const response = await fetch(url);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			return await response.json();
		} catch (error) {
			console.error('ToyLineManager: getAllSimple failed', error);
			return [];
		}
	}

	/**
	 * Clear the cache
	 * Call after create/update/delete operations
	 */
	clearCache() {
		this.manufacturerCache.clear();
	}
}

// Create global instance for collection-forms.js
window.toyLineManager = new ToyLineManager();

console.log('ToyLineManager: Ready (global instance created)');
