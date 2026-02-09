/**
 * UI Helper - Utility functions for common UI operations
 */
class UiHelper {
	/**
	 * Toast configuration defaults
	 */
	static toastDefaults = {
		autohide: true,
		delay: 3000,
	};

	/**
	 * Show a toast notification
	 * @param {string} message - Message to display
	 * @param {string} type - Toast type: success, error, warning, info
	 * @param {Object} options - Additional options
	 * @param {number} [options.delay=3000] - Auto-hide delay in ms
	 * @param {boolean} [options.autohide=true] - Whether to auto-hide
	 */
	static showToast(message, type = 'success', options = {}) {
		// Ensure toast container exists
		let toastContainer = document.getElementById('toast-container');
		if (!toastContainer) {
			toastContainer = document.createElement('div');
			toastContainer.id = 'toast-container';
			toastContainer.className =
				'toast-container position-fixed bottom-0 end-0 p-3';
			toastContainer.style.zIndex = '9999';
			document.body.appendChild(toastContainer);
		}

		// Map types to Bootstrap classes
		const typeMap = {
			success: { bg: 'bg-success', icon: 'fa-check-circle' },
			error: { bg: 'bg-danger', icon: 'fa-exclamation-circle' },
			warning: { bg: 'bg-warning', icon: 'fa-exclamation-triangle' },
			info: { bg: 'bg-info', icon: 'fa-info-circle' },
		};

		const config = typeMap[type] || typeMap.info;

		const toastHtml = `
            <div class="toast align-items-center text-white ${config.bg} border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${config.icon} me-2"></i>
                        ${this.escapeHtml(message)}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

		// Create and append toast
		const toastWrapper = document.createElement('div');
		toastWrapper.innerHTML = toastHtml;
		const toastElement = toastWrapper.firstElementChild;
		toastContainer.appendChild(toastElement);

		// Initialize Bootstrap toast with options
		const toastConfig = {
			...this.toastDefaults,
			...options,
		};
		const bsToast = new bootstrap.Toast(toastElement, toastConfig);
		bsToast.show();

		// Remove from DOM when hidden
		toastElement.addEventListener('hidden.bs.toast', () => {
			toastElement.remove();
		});
	}

	/**
	 * Show success toast
	 * @param {string} message - Success message
	 * @param {Object} options - Toast options
	 */
	static showSuccess(message, options = {}) {
		this.showToast(message, 'success', options);
	}

	/**
	 * Show error toast
	 * @param {string} message - Error message
	 * @param {Object} options - Toast options
	 */
	static showError(message, options = {}) {
		this.showToast(message, 'error', { ...options, delay: 5000 });
	}

	/**
	 * Show warning toast
	 * @param {string} message - Warning message
	 * @param {Object} options - Toast options
	 */
	static showWarning(message, options = {}) {
		this.showToast(message, 'warning', options);
	}

	/**
	 * Show info toast
	 * @param {string} message - Info message
	 * @param {Object} options - Toast options
	 */
	static showInfo(message, options = {}) {
		this.showToast(message, 'info', options);
	}

	/**
	 * Show validation errors as toast messages
	 * @param {Object|Array} errors - Validation errors
	 */
	static showValidationErrors(errors) {
		if (Array.isArray(errors)) {
			errors.forEach((error) => this.showError(error));
		} else if (typeof errors === 'object') {
			Object.values(errors).forEach((errorArray) => {
				if (Array.isArray(errorArray)) {
					errorArray.forEach((error) => this.showError(error));
				} else {
					this.showError(errorArray);
				}
			});
		} else {
			this.showError(errors);
		}
	}

	/**
	 * Show loading spinner in element
	 * @param {string|HTMLElement} target - Element ID or element itself
	 * @param {string} size - Spinner size: sm, md, lg
	 * @param {string} message - Optional loading message
	 */
	static showLoader(target, size = 'md', message = '') {
		const element =
			typeof target === 'string' ? document.getElementById(target) : target;

		if (!element) {
			console.warn('Loader target element not found:', target);
			return;
		}

		const sizeClass = size === 'sm' ? 'spinner-border-sm' : '';
		const loaderHtml = `
            <div class="text-center p-3 loader-container">
                <div class="spinner-border text-primary ${sizeClass}" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message ? `<div class="mt-2 text-muted">${this.escapeHtml(message)}</div>` : ''}
            </div>
        `;

		// Store original content
		element.dataset.originalContent = element.innerHTML;
		element.innerHTML = loaderHtml;
	}

	/**
	 * Hide loading spinner and restore original content
	 * @param {string|HTMLElement} target - Element ID or element itself
	 */
	static hideLoader(target) {
		const element =
			typeof target === 'string' ? document.getElementById(target) : target;

		if (!element) {
			console.warn('Loader target element not found:', target);
			return;
		}

		if (element.dataset.originalContent) {
			element.innerHTML = element.dataset.originalContent;
			delete element.dataset.originalContent;
		} else {
			// If no original content, just remove loader
			const loader = element.querySelector('.loader-container');
			if (loader) {
				loader.remove();
			}
		}
	}

	/**
	 * Show confirmation dialog
	 * @param {string} message - Confirmation message
	 * @param {string} title - Dialog title
	 * @param {Object} options - Additional options
	 * @returns {Promise<boolean>} True if confirmed, false if cancelled
	 */
	static async confirm(message, title = 'Confirm Action', options = {}) {
		return new Promise((resolve) => {
			// Create modal if it doesn't exist
			let modalElement = document.getElementById('confirmModal');

			if (!modalElement) {
				const modalHtml = `
                    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmModalTitle"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="confirmModalBody"></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirmCancelBtn">
                                        ${options.cancelText || 'Cancel'}
                                    </button>
                                    <button type="button" class="btn btn-primary" id="confirmOkBtn">
                                        ${options.confirmText || 'Confirm'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
				document.body.insertAdjacentHTML('beforeend', modalHtml);
				modalElement = document.getElementById('confirmModal');
			}

			// Update content
			document.getElementById('confirmModalTitle').textContent = title;
			document.getElementById('confirmModalBody').innerHTML =
				this.escapeHtml(message);

			// Update button text if provided
			if (options.cancelText) {
				document.getElementById('confirmCancelBtn').textContent =
					options.cancelText;
			}
			if (options.confirmText) {
				document.getElementById('confirmOkBtn').textContent =
					options.confirmText;
			}

			// Handle button classes for danger actions
			const okBtn = document.getElementById('confirmOkBtn');
			okBtn.className = options.danger
				? 'btn btn-danger'
				: 'btn btn-primary';

			// Show modal
			const modal = new bootstrap.Modal(modalElement);
			modal.show();

			// Handle confirmation
			const handleConfirm = () => {
				cleanup();
				modal.hide();
				resolve(true);
			};

			const handleCancel = () => {
				cleanup();
				modal.hide();
				resolve(false);
			};

			const cleanup = () => {
				okBtn.removeEventListener('click', handleConfirm);
				document
					.getElementById('confirmCancelBtn')
					.removeEventListener('click', handleCancel);
				modalElement.removeEventListener('hidden.bs.modal', handleCancel);
			};

			// Attach event listeners
			okBtn.addEventListener('click', handleConfirm);
			document
				.getElementById('confirmCancelBtn')
				.addEventListener('click', handleCancel);
			modalElement.addEventListener('hidden.bs.modal', handleCancel);
		});
	}

	/**
	 * Show delete confirmation dialog
	 * @param {string} itemName - Name of item to delete
	 * @returns {Promise<boolean>} True if confirmed
	 */
	static async confirmDelete(itemName) {
		return this.confirm(
			`Are you sure you want to delete "${itemName}"? This action cannot be undone.`,
			'Confirm Delete',
			{
				confirmText: 'Delete',
				cancelText: 'Cancel',
				danger: true,
			},
		);
	}

	/**
	 * Disable element(s)
	 * @param {string|HTMLElement|NodeList} target - Element(s) to disable
	 */
	static disable(target) {
		const elements = this.getElements(target);
		elements.forEach((el) => {
			el.disabled = true;
			el.classList.add('disabled');
		});
	}

	/**
	 * Enable element(s)
	 * @param {string|HTMLElement|NodeList} target - Element(s) to enable
	 */
	static enable(target) {
		const elements = this.getElements(target);
		elements.forEach((el) => {
			el.disabled = false;
			el.classList.remove('disabled');
		});
	}

	/**
	 * Show element(s)
	 * @param {string|HTMLElement|NodeList} target - Element(s) to show
	 */
	static show(target) {
		const elements = this.getElements(target);
		elements.forEach((el) => {
			el.classList.remove('d-none');
			el.style.display = '';
		});
	}

	/**
	 * Hide element(s)
	 * @param {string|HTMLElement|NodeList} target - Element(s) to hide
	 */
	static hide(target) {
		const elements = this.getElements(target);
		elements.forEach((el) => {
			el.classList.add('d-none');
		});
	}

	/**
	 * Toggle element visibility
	 * @param {string|HTMLElement|NodeList} target - Element(s) to toggle
	 */
	static toggle(target) {
		const elements = this.getElements(target);
		elements.forEach((el) => {
			el.classList.toggle('d-none');
		});
	}

	/**
	 * Fade in element
	 * @param {string|HTMLElement} target - Element to fade in
	 * @param {number} duration - Animation duration in ms
	 */
	static fadeIn(target, duration = 300) {
		const element =
			typeof target === 'string' ? document.getElementById(target) : target;

		if (!element) return;

		element.style.opacity = '0';
		element.style.display = '';
		element.classList.remove('d-none');

		let start = null;
		const animate = (timestamp) => {
			if (!start) start = timestamp;
			const progress = timestamp - start;
			const opacity = Math.min(progress / duration, 1);

			element.style.opacity = opacity;

			if (progress < duration) {
				requestAnimationFrame(animate);
			} else {
				element.style.opacity = '';
			}
		};

		requestAnimationFrame(animate);
	}

	/**
	 * Fade out element
	 * @param {string|HTMLElement} target - Element to fade out
	 * @param {number} duration - Animation duration in ms
	 */
	static fadeOut(target, duration = 300) {
		const element =
			typeof target === 'string' ? document.getElementById(target) : target;

		if (!element) return;

		let start = null;
		const animate = (timestamp) => {
			if (!start) start = timestamp;
			const progress = timestamp - start;
			const opacity = 1 - Math.min(progress / duration, 1);

			element.style.opacity = opacity;

			if (progress < duration) {
				requestAnimationFrame(animate);
			} else {
				element.style.display = 'none';
				element.style.opacity = '';
			}
		};

		requestAnimationFrame(animate);
	}

	/**
	 * Get elements from various input types
	 * @param {string|HTMLElement|NodeList} target - Target element(s)
	 * @returns {Array<HTMLElement>} Array of elements
	 */
	static getElements(target) {
		if (typeof target === 'string') {
			// Try as ID first
			const byId = document.getElementById(target);
			if (byId) return [byId];

			// Try as selector
			return Array.from(document.querySelectorAll(target));
		} else if (target instanceof NodeList) {
			return Array.from(target);
		} else if (target instanceof HTMLElement) {
			return [target];
		}
		return [];
	}

	/**
	 * Escape HTML to prevent XSS
	 * @param {string} text - Text to escape
	 * @returns {string} Escaped text
	 */
	static escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Scroll to element smoothly
	 * @param {string|HTMLElement} target - Element to scroll to
	 * @param {number} offset - Offset from top in pixels
	 */
	static scrollTo(target, offset = 0) {
		const element =
			typeof target === 'string' ? document.getElementById(target) : target;

		if (!element) return;

		const elementPosition = element.getBoundingClientRect().top;
		const offsetPosition = elementPosition + window.pageYOffset - offset;

		window.scrollTo({
			top: offsetPosition,
			behavior: 'smooth',
		});
	}

	/**
	 * Copy text to clipboard
	 * @param {string} text - Text to copy
	 * @returns {Promise<boolean>} True if successful
	 */
	static async copyToClipboard(text) {
		try {
			await navigator.clipboard.writeText(text);
			this.showSuccess('Copied to clipboard');
			return true;
		} catch (error) {
			console.error('Failed to copy to clipboard:', error);
			this.showError('Failed to copy to clipboard');
			return false;
		}
	}

	/**
	 * Format date for display
	 * @param {string|Date} date - Date to format
	 * @param {string} format - Format string (short, long, time)
	 * @returns {string} Formatted date
	 */
	static formatDate(date, format = 'short') {
		const dateObj = typeof date === 'string' ? new Date(date) : date;

		if (!(dateObj instanceof Date) || isNaN(dateObj)) {
			return '';
		}

		const options = {
			short: { year: 'numeric', month: '2-digit', day: '2-digit' },
			long: { year: 'numeric', month: 'long', day: 'numeric' },
			time: { hour: '2-digit', minute: '2-digit' },
			full: {
				year: 'numeric',
				month: 'long',
				day: 'numeric',
				hour: '2-digit',
				minute: '2-digit',
			},
		};

		return dateObj.toLocaleString('en-US', options[format] || options.short);
	}

	/**
	 * Format currency
	 * @param {number} amount - Amount to format
	 * @param {string} currency - Currency code (USD, EUR, DKK)
	 * @returns {string} Formatted currency
	 */
	static formatCurrency(amount, currency = 'DKK') {
		return new Intl.NumberFormat('da-DK', {
			style: 'currency',
			currency: currency,
		}).format(amount);
	}

	/**
	 * Debounce function calls
	 * @param {Function} func - Function to debounce
	 * @param {number} wait - Wait time in ms
	 * @returns {Function} Debounced function
	 */
	static debounce(func, wait = 300) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	}
}

// Make globally available
window.UiHelper = UiHelper;
