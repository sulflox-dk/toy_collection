/**
 * API Client for handling HTTP requests
 * Provides a consistent interface for all API communications
 */
class ApiClient {
	/**
	 * Base URL for API requests
	 * @type {string}
	 */
	static baseUrl = '/index.php';

	/**
	 * Default request timeout in milliseconds
	 * @type {number}
	 */
	static timeout = 30000;

	/**
	 * Perform an HTTP request
	 * @param {string} url - Request URL
	 * @param {Object} options - Request options
	 * @param {string} [options.method='GET'] - HTTP method
	 * @param {Object|FormData} [options.body] - Request body
	 * @param {Object} [options.headers={}] - Additional headers
	 * @param {number} [options.timeout] - Request timeout
	 * @returns {Promise<Object|string>} Response data
	 * @throws {ApiError} If request fails
	 */
	static async request(url, options = {}) {
		const defaultHeaders = {
			'X-Requested-With': 'XMLHttpRequest',
		};

		// Don't set Content-Type for FormData - browser will set it with boundary
		if (!(options.body instanceof FormData)) {
			defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		const config = {
			method: options.method || 'GET',
			headers: { ...defaultHeaders, ...options.headers },
		};

		// Handle request body
		if (options.body) {
			if (options.body instanceof FormData) {
				config.body = options.body;
			} else if (typeof options.body === 'object') {
				// Convert object to URL-encoded string for PHP $_POST
				config.body = new URLSearchParams(options.body).toString();
			} else {
				config.body = options.body;
			}
		}

		// Add timeout support
		const timeout = options.timeout || this.timeout;
		const controller = new AbortController();
		const timeoutId = setTimeout(() => controller.abort(), timeout);
		config.signal = controller.signal;

		try {
			const response = await fetch(url, config);
			clearTimeout(timeoutId);

			// Handle HTTP errors
			if (!response.ok) {
				const errorData = await this.parseResponse(response);
				throw new ApiError(
					errorData.message || `HTTP Error: ${response.status}`,
					response.status,
					errorData,
				);
			}

			// Parse and return response
			return await this.parseResponse(response);
		} catch (error) {
			clearTimeout(timeoutId);

			if (error.name === 'AbortError') {
				throw new ApiError('Request timeout', 408);
			}

			if (error instanceof ApiError) {
				throw error;
			}

			console.error('API Request Failed:', error);
			throw new ApiError('Network error occurred', 0, {
				originalError: error,
			});
		}
	}

	/**
	 * Parse response based on content type
	 * @param {Response} response - Fetch response object
	 * @returns {Promise<Object|string>} Parsed response
	 */
	static async parseResponse(response) {
		const contentType = response.headers.get('content-type');

		if (contentType && contentType.includes('application/json')) {
			return await response.json();
		}

		return await response.text();
	}

	/**
	 * Build URL with query parameters
	 * @param {string} baseUrl - Base URL
	 * @param {Object} params - Query parameters
	 * @returns {string} Complete URL
	 */
	static buildUrl(baseUrl, params = {}) {
		const queryString = new URLSearchParams(params).toString();

		// Handle both cases: URL already has params or not
		const separator = baseUrl.includes('?') ? '&' : '?';

		return queryString ? `${baseUrl}${separator}${queryString}` : baseUrl;
	}

	/**
	 * Build module URL
	 * @param {string} module - Module name
	 * @param {string} controller - Controller name
	 * @param {string} action - Action name
	 * @param {Object} params - Additional query parameters
	 * @returns {string} Complete URL
	 */
	static buildModuleUrl(module, controller, action, params = {}) {
		const baseParams = {
			module,
			controller,
			action,
			...params,
		};
		return this.buildUrl(this.baseUrl, baseParams);
	}

	/**
	 * Perform GET request
	 * @param {string} url - Request URL
	 * @param {Object} params - Query parameters
	 * @returns {Promise<Object|string>} Response data
	 */
	static get(url, params = {}) {
		const fullUrl = this.buildUrl(url, params);
		return this.request(fullUrl, { method: 'GET' });
	}

	/**
	 * Perform POST request
	 * @param {string} url - Request URL
	 * @param {Object|FormData} data - Request body
	 * @returns {Promise<Object>} Response data
	 */
	static post(url, data) {
		return this.request(url, { method: 'POST', body: data });
	}

	/**
	 * Perform PUT request
	 * @param {string} url - Request URL
	 * @param {Object|FormData} data - Request body
	 * @returns {Promise<Object>} Response data
	 */
	static put(url, data) {
		return this.request(url, { method: 'PUT', body: data });
	}

	/**
	 * Perform DELETE request
	 * @param {string} url - Request URL
	 * @param {Object} params - Query parameters
	 * @returns {Promise<Object>} Response data
	 */
	static delete(url, params = {}) {
		const fullUrl = this.buildUrl(url, params);
		return this.request(fullUrl, { method: 'DELETE' });
	}

	/**
	 * Upload file(s)
	 * @param {string} url - Upload URL
	 * @param {File|File[]} files - File(s) to upload
	 * @param {Object} additionalData - Additional form data
	 * @returns {Promise<Object>} Upload response
	 */
	static async uploadFiles(url, files, additionalData = {}) {
		const formData = new FormData();

		// Add files
		if (Array.isArray(files)) {
			files.forEach((file, index) => {
				formData.append(`files[${index}]`, file);
			});
		} else {
			formData.append('file', files);
		}

		// Add additional data
		Object.keys(additionalData).forEach((key) => {
			formData.append(key, additionalData[key]);
		});

		return this.post(url, formData);
	}
}

/**
 * Custom API Error class
 */
class ApiError extends Error {
	/**
	 * @param {string} message - Error message
	 * @param {number} status - HTTP status code
	 * @param {Object} data - Additional error data
	 */
	constructor(message, status = 0, data = {}) {
		super(message);
		this.name = 'ApiError';
		this.status = status;
		this.data = data;
	}

	/**
	 * Check if error is a validation error
	 * @returns {boolean}
	 */
	isValidationError() {
		return this.status === 422;
	}

	/**
	 * Check if error is not found
	 * @returns {boolean}
	 */
	isNotFound() {
		return this.status === 404;
	}

	/**
	 * Check if error is unauthorized
	 * @returns {boolean}
	 */
	isUnauthorized() {
		return this.status === 401 || this.status === 403;
	}

	/**
	 * Check if error is server error
	 * @returns {boolean}
	 */
	isServerError() {
		return this.status >= 500;
	}
}

// Make globally available
window.ApiClient = ApiClient;
window.ApiError = ApiError;
