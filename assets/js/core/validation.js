/**
 * Validation - Client-side form validation utility
 */
class Validation {
	/**
	 * Validation rules with their validators and error messages
	 */
	static rules = {
		required: {
			validate: (value) =>
				value !== null && value !== undefined && value.trim() !== '',
			message: 'This field is required',
		},
		email: {
			validate: (value) => {
				const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				return !value || emailRegex.test(value);
			},
			message: 'Please enter a valid email address',
		},
		url: {
			validate: (value) => {
				try {
					if (!value) return true;
					new URL(value);
					return true;
				} catch {
					return false;
				}
			},
			message: 'Please enter a valid URL',
		},
		numeric: {
			validate: (value) => !value || !isNaN(value),
			message: 'Please enter a valid number',
		},
		integer: {
			validate: (value) =>
				!value || (Number.isInteger(Number(value)) && !value.includes('.')),
			message: 'Please enter a whole number',
		},
		min: {
			validate: (value, param) => !value || value.length >= parseInt(param),
			message: (param) => `Minimum ${param} characters required`,
		},
		max: {
			validate: (value, param) => !value || value.length <= parseInt(param),
			message: (param) => `Maximum ${param} characters allowed`,
		},
		minValue: {
			validate: (value, param) =>
				!value || parseFloat(value) >= parseFloat(param),
			message: (param) => `Value must be at least ${param}`,
		},
		maxValue: {
			validate: (value, param) =>
				!value || parseFloat(value) <= parseFloat(param),
			message: (param) => `Value must be at most ${param}`,
		},
		pattern: {
			validate: (value, param) => !value || new RegExp(param).test(value),
			message: 'Please match the requested format',
		},
		matches: {
			validate: (value, param, form) => {
				const matchField = form.querySelector(`[name="${param}"]`);
				return !value || !matchField || value === matchField.value;
			},
			message: (param) => `Must match ${param}`,
		},
		date: {
			validate: (value) => {
				if (!value) return true;
				const date = new Date(value);
				return date instanceof Date && !isNaN(date);
			},
			message: 'Please enter a valid date',
		},
		minDate: {
			validate: (value, param) => {
				if (!value) return true;
				const inputDate = new Date(value);
				const minDate = new Date(param);
				return inputDate >= minDate;
			},
			message: (param) => `Date must be on or after ${param}`,
		},
		maxDate: {
			validate: (value, param) => {
				if (!value) return true;
				const inputDate = new Date(value);
				const maxDate = new Date(param);
				return inputDate <= maxDate;
			},
			message: (param) => `Date must be on or before ${param}`,
		},
	};

	/**
	 * Validate a form
	 * @param {string|HTMLFormElement} form - Form ID or element
	 * @param {Object} options - Validation options
	 * @param {boolean} [options.showToast=true] - Show error toast
	 * @param {boolean} [options.scrollToError=true] - Scroll to first error
	 * @param {boolean} [options.focusError=true] - Focus first error field
	 * @returns {boolean} True if valid
	 */
	static validateForm(form, options = {}) {
		const defaults = {
			showToast: true,
			scrollToError: true,
			focusError: true,
		};
		const config = { ...defaults, ...options };

		const formElement =
			typeof form === 'string' ? document.getElementById(form) : form;

		if (!formElement) {
			console.error('Form not found:', form);
			return false;
		}

		let isValid = true;
		let firstInvalidField = null;
		const errors = [];

		// Clear previous validation states
		this.clearValidation(formElement);

		// Get all inputs, selects, and textareas
		const fields = formElement.querySelectorAll('input, select, textarea');

		fields.forEach((field) => {
			const fieldErrors = this.validateField(field, formElement);

			if (fieldErrors.length > 0) {
				isValid = false;

				// Mark field as invalid
				field.classList.add('is-invalid');

				// Show error message
				this.showFieldError(field, fieldErrors[0]);

				// Track first invalid field
				if (!firstInvalidField) {
					firstInvalidField = field;
				}

				// Collect errors
				errors.push(...fieldErrors);
			} else {
				field.classList.add('is-valid');
			}
		});

		// Handle validation result
		if (!isValid) {
			if (config.showToast) {
				const errorMessage =
					errors.length === 1
						? errors[0]
						: `Please fix ${errors.length} validation errors`;
				UiHelper.showError(errorMessage);
			}

			if (firstInvalidField) {
				if (config.scrollToError) {
					UiHelper.scrollTo(firstInvalidField, 100);
				}
				if (config.focusError) {
					firstInvalidField.focus();
				}
			}
		}

		return isValid;
	}

	/**
	 * Validate a single field
	 * @param {HTMLElement} field - Field to validate
	 * @param {HTMLFormElement} form - Parent form
	 * @returns {Array<string>} Array of error messages
	 */
	static validateField(field, form = null) {
		const errors = [];
		const value = field.value;
		const fieldName =
			field.getAttribute('data-name') || field.name || field.id;

		// Check required
		if (field.hasAttribute('required')) {
			const rule = this.rules.required;
			if (!rule.validate(value)) {
				errors.push(this.getErrorMessage(field, 'required', rule.message));
			}
		}

		// If field is empty and not required, skip other validations
		if (!value && !field.hasAttribute('required')) {
			return errors;
		}

		// Check type-based validations
		const type = field.getAttribute('type');
		if (type === 'email' && this.rules.email) {
			if (!this.rules.email.validate(value)) {
				errors.push(
					this.getErrorMessage(field, 'email', this.rules.email.message),
				);
			}
		}
		if (type === 'url' && this.rules.url) {
			if (!this.rules.url.validate(value)) {
				errors.push(
					this.getErrorMessage(field, 'url', this.rules.url.message),
				);
			}
		}
		if (type === 'number' || field.hasAttribute('data-numeric')) {
			if (!this.rules.numeric.validate(value)) {
				errors.push(
					this.getErrorMessage(
						field,
						'numeric',
						this.rules.numeric.message,
					),
				);
			}
		}

		// Check custom data attributes
		Object.keys(this.rules).forEach((ruleName) => {
			const attr = `data-${ruleName.toLowerCase()}`;
			if (field.hasAttribute(attr)) {
				const param = field.getAttribute(attr);
				const rule = this.rules[ruleName];

				const isValid = param
					? rule.validate(value, param, form)
					: rule.validate(value);

				if (!isValid) {
					const message =
						typeof rule.message === 'function'
							? rule.message(param)
							: rule.message;
					errors.push(this.getErrorMessage(field, ruleName, message));
				}
			}
		});

		// Check HTML5 validations
		if (field.hasAttribute('minlength')) {
			const minLength = parseInt(field.getAttribute('minlength'));
			if (value && value.length < minLength) {
				errors.push(
					this.getErrorMessage(
						field,
						'minlength',
						`Minimum ${minLength} characters required`,
					),
				);
			}
		}

		if (field.hasAttribute('maxlength')) {
			const maxLength = parseInt(field.getAttribute('maxlength'));
			if (value && value.length > maxLength) {
				errors.push(
					this.getErrorMessage(
						field,
						'maxlength',
						`Maximum ${maxLength} characters allowed`,
					),
				);
			}
		}

		if (field.hasAttribute('pattern')) {
			const pattern = field.getAttribute('pattern');
			if (value && !new RegExp(pattern).test(value)) {
				const patternMessage =
					field.getAttribute('data-pattern-message') ||
					'Please match the requested format';
				errors.push(this.getErrorMessage(field, 'pattern', patternMessage));
			}
		}

		return errors;
	}

	/**
	 * Get error message for a field
	 * @param {HTMLElement} field - Field element
	 * @param {string} ruleName - Validation rule name
	 * @param {string} defaultMessage - Default error message
	 * @returns {string} Error message
	 */
	static getErrorMessage(field, ruleName, defaultMessage) {
		// Check for custom message on field
		const customMessage = field.getAttribute(`data-${ruleName}-message`);
		if (customMessage) {
			return customMessage;
		}

		// Use field name in message if available
		const fieldName =
			field.getAttribute('data-name') ||
			field.getAttribute('placeholder') ||
			field.name ||
			field.id;

		// Enhance default message with field name
		if (fieldName && !defaultMessage.includes(fieldName)) {
			return `${fieldName}: ${defaultMessage}`;
		}

		return defaultMessage;
	}

	/**
	 * Show error message for a field
	 * @param {HTMLElement} field - Field element
	 * @param {string} message - Error message
	 */
	static showFieldError(field, message) {
		// Look for existing feedback element
		let feedback = field.parentElement.querySelector('.invalid-feedback');

		if (!feedback) {
			// Create feedback element
			feedback = document.createElement('div');
			feedback.className = 'invalid-feedback';

			// Insert after field or after input-group
			const inputGroup = field.closest('.input-group');
			if (inputGroup) {
				inputGroup.insertAdjacentElement('afterend', feedback);
			} else {
				field.insertAdjacentElement('afterend', feedback);
			}
		}

		feedback.textContent = message;
		feedback.style.display = 'block';
	}

	/**
	 * Clear validation state from form
	 * @param {string|HTMLFormElement} form - Form ID or element
	 */
	static clearValidation(form) {
		const formElement =
			typeof form === 'string' ? document.getElementById(form) : form;

		if (!formElement) return;

		// Remove validation classes
		formElement.querySelectorAll('.is-invalid, .is-valid').forEach((el) => {
			el.classList.remove('is-invalid', 'is-valid');
		});

		// Hide feedback messages
		formElement.querySelectorAll('.invalid-feedback').forEach((el) => {
			el.style.display = 'none';
		});
	}

	/**
	 * Add real-time validation to a form
	 * @param {string|HTMLFormElement} form - Form ID or element
	 * @param {Object} options - Validation options
	 */
	static addLiveValidation(form, options = {}) {
		const formElement =
			typeof form === 'string' ? document.getElementById(form) : form;

		if (!formElement) return;

		const fields = formElement.querySelectorAll('input, select, textarea');

		fields.forEach((field) => {
			// Validate on blur
			field.addEventListener('blur', () => {
				this.validateSingleField(field, formElement);
			});

			// Clear error on input
			field.addEventListener('input', () => {
				if (field.classList.contains('is-invalid')) {
					field.classList.remove('is-invalid');
					const feedback =
						field.parentElement.querySelector('.invalid-feedback');
					if (feedback) {
						feedback.style.display = 'none';
					}
				}
			});
		});

		// Prevent form submission if invalid
		formElement.addEventListener('submit', (e) => {
			if (!this.validateForm(formElement, options)) {
				e.preventDefault();
				e.stopPropagation();
			}
		});
	}

	/**
	 * Validate a single field and show result
	 * @param {HTMLElement} field - Field to validate
	 * @param {HTMLFormElement} form - Parent form
	 * @returns {boolean} True if valid
	 */
	static validateSingleField(field, form = null) {
		const errors = this.validateField(field, form);

		if (errors.length > 0) {
			field.classList.add('is-invalid');
			field.classList.remove('is-valid');
			this.showFieldError(field, errors[0]);
			return false;
		} else {
			field.classList.remove('is-invalid');
			field.classList.add('is-valid');
			const feedback =
				field.parentElement.querySelector('.invalid-feedback');
			if (feedback) {
				feedback.style.display = 'none';
			}
			return true;
		}
	}

	/**
	 * Add custom validation rule
	 * @param {string} name - Rule name
	 * @param {Function} validator - Validation function
	 * @param {string|Function} message - Error message
	 */
	static addRule(name, validator, message) {
		this.rules[name] = {
			validate: validator,
			message: message,
		};
	}

	/**
	 * Validate email format
	 * @param {string} email - Email to validate
	 * @returns {boolean} True if valid
	 */
	static isValidEmail(email) {
		return this.rules.email.validate(email);
	}

	/**
	 * Validate URL format
	 * @param {string} url - URL to validate
	 * @returns {boolean} True if valid
	 */
	static isValidUrl(url) {
		return this.rules.url.validate(url);
	}

	/**
	 * Check if value is numeric
	 * @param {*} value - Value to check
	 * @returns {boolean} True if numeric
	 */
	static isNumeric(value) {
		return this.rules.numeric.validate(value);
	}

	/**
	 * Check if value is integer
	 * @param {*} value - Value to check
	 * @returns {boolean} True if integer
	 */
	static isInteger(value) {
		return this.rules.integer.validate(value);
	}
}

// Make globally available
window.Validation = Validation;
