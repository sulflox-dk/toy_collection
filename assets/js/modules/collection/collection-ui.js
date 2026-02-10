/**
 * Collection UI Module
 * Handles UI rendering and updates for the Collection module
 */
const CollectionUi = {
	/**
	 * Current view mode (list or cards)
	 */
	viewMode: 'list',

	/**
	 * Container element
	 */
	container: null,

	/**
	 * Initialize UI module
	 */
	init() {
		this.container = document.getElementById('collectionGridContainer');

		// Get view mode from cookie
		const match = document.cookie.match(
			new RegExp('(^| )collection_view_mode=([^;]+)'),
		);
		this.viewMode = match ? match[2] : 'list';

		this.updateViewButtons(this.viewMode);
		this.attachViewModeListeners();
	},

	/**
	 * Attach view mode button listeners
	 */
	attachViewModeListeners() {
		const btnList = document.getElementById('btn-view-list');
		const btnCards = document.getElementById('btn-view-cards');

		if (btnList) {
			btnList.addEventListener('click', () => this.switchView('list'));
		}

		if (btnCards) {
			btnCards.addEventListener('click', () => this.switchView('cards'));
		}
	},

	/**
	 * Switch view mode
	 * @param {string} mode - View mode (list or cards)
	 */
	switchView(mode) {
		this.viewMode = mode;
		document.cookie = `collection_view_mode=${mode}; path=/; max-age=31536000`;
		this.updateViewButtons(mode);

		// Trigger reload in parent manager
		if (
			window.CollectionManager &&
			typeof CollectionManager.loadPage === 'function'
		) {
			CollectionManager.loadPage(CollectionManager.currentPage || 1);
		}
	},

	/**
	 * Update view button states
	 * @param {string} mode - Active view mode
	 */
	updateViewButtons(mode) {
		const btnList = document.getElementById('btn-view-list');
		const btnCards = document.getElementById('btn-view-cards');

		if (btnList && btnCards) {
			if (mode === 'list') {
				btnList.classList.add('active', 'bg-secondary', 'text-white');
				btnCards.classList.remove('active', 'bg-secondary', 'text-white');
			} else {
				btnCards.classList.add('active', 'bg-secondary', 'text-white');
				btnList.classList.remove('active', 'bg-secondary', 'text-white');
			}
		}
	},

	/**
	 * Render grid with toy data
	 * @param {Object} data - Data object with toys array, pagination, and view_mode
	 */
	renderGrid(html) {
		if (!this.container) return;

		// Insert server-rendered HTML directly
		this.container.innerHTML = html;

		// Attach pagination click handlers (pagination is rendered by PHP)
		this.container.querySelectorAll('.page-link').forEach((link) => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				// Extract page number from onclick or href
				const onclickAttr = link.getAttribute('onclick');
				if (onclickAttr) {
					const match = onclickAttr.match(/loadPage\((\d+)\)/);
					if (match && window.CollectionManager) {
						window.CollectionManager.loadPage(parseInt(match[1]));
					}
				}
			});
		});

		UiHelper.fadeIn(this.container, 200);
	},

	/**
	 * Show loading state
	 */
	showLoading() {
		if (this.container) {
			this.container.style.opacity = '0.5';
		}
	},

	/**
	 * Hide loading state
	 */
	hideLoading() {
		if (this.container) {
			this.container.style.opacity = '1';
		}
	},

	/**
	 * Render error message
	 * @param {string} message - Error message
	 */
	renderError(message) {
		if (!this.container) return;

		this.container.innerHTML = `
            <div class="alert alert-danger p-3">
                <i class="fas fa-exclamation-circle me-2"></i>
                ${UiHelper.escapeHtml(message)}
            </div>
        `;
	},

	/**
	 * Render empty state
	 */
	renderEmpty() {
		if (!this.container) return;

		this.container.innerHTML = `
            <div class="alert alert-info p-3">
                <i class="fas fa-info-circle me-2"></i>
                No toys found. Try adjusting your filters or add a new toy to your collection.
            </div>
        `;
	},

	/**
	 * Render cards view
	 * @param {Array} toys - Array of toy objects
	 */
	renderCardsView(toys) {
		const row = document.createElement('div');
		row.className = 'row g-3 p-3';

		toys.forEach((toy) => {
			const col = document.createElement('div');
			col.className = 'col-md-6 col-lg-4 col-xl-3';
			col.innerHTML = this.createToyCard(toy);
			row.appendChild(col);
		});

		this.container.appendChild(row);
	},

	/**
	 * Render list view
	 * @param {Array} toys - Array of toy objects
	 */
	renderListView(toys) {
		const table = document.createElement('table');
		table.className = 'table table-hover mb-0';

		table.innerHTML = `
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Toy</th>
                    <th>Line</th>
                    <th>Status</th>
                    <th>Completeness</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                ${toys.map((toy) => this.createToyRow(toy)).join('')}
            </tbody>
        `;

		this.container.appendChild(table);
	},

	/**
	 * Create toy card HTML
	 * @param {Object} toy - Toy data object
	 * @returns {string} Card HTML
	 */
	createToyCard(toy) {
		const imageSrc =
			toy.main_image || toy.stock_image || '/assets/images/no-image.png';
		const statusBadge = this.getStatusBadge(toy.acquisition_status);
		const completenessBadge = this.getCompletenessBadge(
			toy.completeness_grade,
		);

		return `
            <div class="card toy-card h-100" data-id="${toy.id}">
                <img src="${UiHelper.escapeHtml(imageSrc)}" 
                     class="card-img-top" 
                     alt="${UiHelper.escapeHtml(toy.master_toy_name)}"
                     style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h6 class="card-title">${UiHelper.escapeHtml(toy.master_toy_name)}</h6>
                    <p class="card-text small text-muted mb-2">
                        <strong>${UiHelper.escapeHtml(toy.line_name || '')}</strong><br>
                        ${UiHelper.escapeHtml(toy.manufacturer_name || '')}
                    </p>
                    <div class="mb-2">
                        ${statusBadge}
                        ${completenessBadge}
                    </div>
                    ${toy.purchase_price ? `<p class="text-muted small mb-2">Price: ${UiHelper.formatCurrency(toy.purchase_price)}</p>` : ''}
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button class="btn btn-outline-primary btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-media" title="Media">
                            <i class="fas fa-image"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
	},

	/**
	 * Create toy table row HTML
	 * @param {Object} toy - Toy data object
	 * @returns {string} Row HTML
	 */
	createToyRow(toy) {
		const imageSrc =
			toy.main_image || toy.stock_image || '/assets/images/no-image.png';
		const statusBadge = this.getStatusBadge(toy.acquisition_status);
		const completenessBadge = this.getCompletenessBadge(
			toy.completeness_grade,
		);

		return `
            <tr data-id="${toy.id}">
                <td>
                    <img src="${UiHelper.escapeHtml(imageSrc)}" 
                         alt="${UiHelper.escapeHtml(toy.master_toy_name)}"
                         class="img-thumbnail"
                         style="width: 50px; height: 50px; object-fit: cover;">
                </td>
                <td>
                    <strong>${UiHelper.escapeHtml(toy.master_toy_name)}</strong><br>
                    <small class="text-muted">${UiHelper.escapeHtml(toy.manufacturer_name || '')}</small>
                </td>
                <td>${UiHelper.escapeHtml(toy.line_name || '')}</td>
                <td>${statusBadge}</td>
                <td>${completenessBadge}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-media" title="Media">
                            <i class="fas fa-image"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
	},

	/**
	 * Get status badge HTML
	 * @param {string} status - Acquisition status
	 * @returns {string} Badge HTML
	 */
	getStatusBadge(status) {
		const badges = {
			Arrived: 'bg-success',
			Ordered: 'bg-warning text-dark',
			'Pre-ordered': 'bg-info',
		};

		const bgClass = badges[status] || 'bg-secondary';
		return `<span class="badge ${bgClass}">${UiHelper.escapeHtml(status || 'Unknown')}</span>`;
	},

	/**
	 * Get completeness badge HTML
	 * @param {string} grade - Completeness grade
	 * @returns {string} Badge HTML
	 */
	getCompletenessBadge(grade) {
		const badges = {
			Complete: 'bg-success',
			Incomplete: 'bg-warning text-dark',
			Sealed: 'bg-primary',
			Custom: 'bg-info',
		};

		const bgClass = badges[grade] || 'bg-secondary';
		return `<span class="badge ${bgClass}">${UiHelper.escapeHtml(grade || 'Unknown')}</span>`;
	},

	/**
	 * Render pagination
	 * @param {Object} pagination - Pagination data
	 */
	renderPagination(pagination) {
		if (pagination.total_pages <= 1) return;

		const paginationHtml = `
            <nav class="p-3">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                    </li>
                    ${this.createPageNumbers(pagination.current_page, pagination.total_pages)}
                    <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                    </li>
                </ul>
                <p class="text-center text-muted small mt-2 mb-0">
                    Showing page ${pagination.current_page} of ${pagination.total_pages} 
                    (${pagination.total_items} total toys)
                </p>
            </nav>
        `;

		const paginationDiv = document.createElement('div');
		paginationDiv.innerHTML = paginationHtml;
		this.container.appendChild(paginationDiv);

		// Attach click handlers
		paginationDiv.querySelectorAll('.page-link').forEach((link) => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				const page = parseInt(link.dataset.page);
				if (page && page > 0 && page <= pagination.total_pages) {
					if (window.CollectionManager) {
						window.CollectionManager.loadPage(page);
					}
				}
			});
		});
	},

	/**
	 * Create page number links
	 * @param {number} currentPage - Current page number
	 * @param {number} totalPages - Total number of pages
	 * @returns {string} Page number HTML
	 */
	createPageNumbers(currentPage, totalPages) {
		const maxVisible = 5;
		let pages = '';

		let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
		let endPage = Math.min(totalPages, startPage + maxVisible - 1);

		if (endPage - startPage < maxVisible - 1) {
			startPage = Math.max(1, endPage - maxVisible + 1);
		}

		for (let i = startPage; i <= endPage; i++) {
			const active = i === currentPage ? 'active' : '';
			pages += `
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
		}

		return pages;
	},

	/**
	 * Refresh single item in the grid
	 * @param {number} id - Item ID
	 * @param {Object} data - Toy data object with toy and view_mode
	 */
	refreshItem(id, data) {
		// Find the item element (could be card or table row)
		let oldEl = null;

		if (this.container) {
			oldEl = this.container.querySelector(`[data-id="${id}"]`);
		}

		if (!oldEl) {
			oldEl = document.querySelector(
				`.toy-card[data-id="${id}"], tr[data-id="${id}"]`,
			);
		}

		if (!oldEl) {
			console.log('CollectionUi: Item not found in grid, skipping refresh');
			return;
		}

		const { toy, view_mode } = data;

		// Create new element based on view mode
		let newEl;
		if (view_mode === 'cards' || oldEl.classList.contains('toy-card')) {
			// Card view
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = this.createToyCard(toy);
			newEl = tempDiv.firstElementChild;
		} else {
			// List view
			const tempTr = document.createElement('tr');
			tempTr.innerHTML = this.createToyRow(toy);
			newEl = tempTr;
			// Extract the actual tr from the wrapper
			if (
				tempTr.firstElementChild &&
				tempTr.firstElementChild.tagName === 'TR'
			) {
				newEl = tempTr.firstElementChild;
			}
		}

		if (newEl) {
			// Replace old with new
			oldEl.replaceWith(newEl);

			// Flash effect
			this.flashElement(newEl);
		} else {
			console.error('CollectionUi: Could not create new element');
		}
	},

	/**
	 * Flash element to indicate update
	 * @param {HTMLElement} element - Element to flash
	 */
	flashElement(element) {
		element.style.transition = 'background-color 0.5s ease';
		const isRow = element.tagName === 'TR';
		const flashColor = isRow ? '#f8f9fa' : '#e8f5e9';

		const originalBg = element.style.backgroundColor;
		element.style.backgroundColor = flashColor;

		setTimeout(() => {
			element.style.backgroundColor = originalBg || '';
		}, 800);
	},

	/**
	 * Remove item from DOM with animation
	 * @param {number} id - Item ID
	 */
	removeItem(id) {
		const element = this.container
			? this.container.querySelector(`[data-id="${id}"]`)
			: document.querySelector(
					`.toy-card[data-id="${id}"], tr[data-id="${id}"]`,
				);

		if (element) {
			UiHelper.fadeOut(element, 300);
			setTimeout(() => element.remove(), 300);
		}
	},

	/**
	 * Update item count badge
	 * @param {number} count - Item count
	 */
	updateItemCount(count) {
		const badge = document.getElementById('itemCountBadge');
		if (badge) {
			badge.textContent = count;
		}
	},

	/**
	 * Scroll to element
	 * @param {string|HTMLElement} target - Element to scroll to
	 */
	scrollTo(target) {
		UiHelper.scrollTo(target, 100);
	},
};

// Make globally available
window.CollectionUi = CollectionUi;
