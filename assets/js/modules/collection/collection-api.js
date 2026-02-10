/**
 * Collection API Module
 * Handles all API calls for the Collection module
 */
const CollectionApi = {
	/**
	 * Get all toys with filters and pagination
	 * @param {Object} filters - Filter criteria
	 * @param {number} page - Page number
	 * @returns {Promise<Object>} Response with toys array and pagination info
	 */
	async getAll(filters = {}, page = 1) {
		const params = {
			ajax_grid: 1,
			page: page,
			universe_id: filters.universe_id || '',
			line_id: filters.line_id || '',
			ent_source_id: filters.ent_source_id || '',
			storage_id: filters.storage_id || '',
			source_id: filters.source_id || '',
			status: filters.status || '',
			manufacturer_id: filters.manufacturer_id || '',
			product_type_id: filters.product_type_id || '',
			completeness: filters.completeness || '',
			missing_parts: filters.missing_parts || '',
			image_status: filters.image_status || '',
			search: filters.search || '',
		};

		const url = ApiClient.buildModuleUrl('Collection', 'Toy', 'index');
		return await ApiClient.fetchHtml(url, params);
	},

	/**
	 * Get single toy by ID
	 * @param {number} id - Toy ID
	 * @returns {Promise<Object>} Toy data object
	 */
	async getById(id) {
		const url = ApiClient.buildModuleUrl(
			'Collection',
			'Toy',
			'get_item_html',
		);
		const queryString = new URLSearchParams({
			id: id,
			t: Date.now(),
		}).toString();
		const fullUrl = url + '&' + queryString;

		const response = await fetch(fullUrl);

		if (!response.ok) {
			throw new Error('Failed to load toy');
		}

		// This endpoint still returns JSON with toy data for single item refresh
		return await response.json();
	},

	/**
	 * Create new collection toy
	 * @param {Object} data - Toy data
	 * @returns {Promise<Object>} Created toy data
	 */
	async create(data) {
		const url = ApiClient.buildModuleUrl('Collection', 'Toy', 'save');
		return await ApiClient.post(url, data);
	},

	/**
	 * Update existing collection toy
	 * @param {number} id - Toy ID
	 * @param {Object} data - Updated toy data
	 * @returns {Promise<Object>} Updated toy data
	 */
	async update(id, data) {
		const url = ApiClient.buildModuleUrl('Collection', 'Toy', 'save');
		return await ApiClient.post(url, { id, ...data });
	},

	/**
	 * Delete collection toy
	 * @param {number} id - Toy ID
	 * @returns {Promise<Object>} Delete response
	 */
	async delete(id) {
		const url = ApiClient.buildModuleUrl('Collection', 'Toy', 'delete');
		return await ApiClient.post(url, { id });
	},

	/**
	 * Delete collection item
	 * @param {number} itemId - Item ID
	 * @returns {Promise<Object>} Delete response
	 */
	async deleteItem(itemId) {
		const url = ApiClient.buildModuleUrl('Collection', 'Api', 'delete_item');
		return await ApiClient.post(url, { id: itemId });
	},

	/**
	 * Get master toy items
	 * @param {number} masterToyId - Master toy ID
	 * @returns {Promise<Array>} Array of master toy items
	 */
	async getMasterToyItems(masterToyId) {
		const url = ApiClient.buildModuleUrl(
			'Collection',
			'Api',
			'get_master_toy_items',
		);
		const response = await ApiClient.get(url, { master_toy_id: masterToyId });
		return Array.isArray(response) ? response : [];
	},

	/**
	 * Delete media file
	 * @param {number} mediaId - Media ID
	 * @returns {Promise<Object>} Delete response
	 */
	async deleteMedia(mediaId) {
		const url = ApiClient.buildModuleUrl('Collection', 'Api', 'delete_media');
		return await ApiClient.post(url, { id: mediaId });
	},
};

// Make globally available
window.CollectionApi = CollectionApi;
