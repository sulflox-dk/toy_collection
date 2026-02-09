const App = {
	// Tjek at URL'en passer til din installation
	baseUrl: typeof SITE_URL !== 'undefined' ? SITE_URL : '/',

	/**
	 * Åbner den globale modal og henter indhold
	 */
	openModal: function (module, controller, action, params = {}) {
		const modalEl = document.getElementById('appModal');

		// 1. FIX: Tjek om modalen allerede findes/er åben
		let modal = bootstrap.Modal.getInstance(modalEl);

		if (!modal) {
			// Hvis den ikke findes endnu, opret den og vis
			modal = new bootstrap.Modal(modalEl);
			modal.show();
		} else {
			// Hvis den findes, men er lukket, så vis den.
			// Hvis den allerede ER åben (fordi vi skifter trin), gør vi ingenting (ingen ekstra backdrop)
			if (!modalEl.classList.contains('show')) {
				modal.show();
			}
		}

		// 2. Vis loader mens vi henter det nye indhold
		modalEl.querySelector('.modal-content').innerHTML = `
            <div class="modal-header border-0 bg-dark text-white">
                <h5 class="modal-title">Loading...</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `;

		// 3. Byg URL
		let url = `${this.baseUrl}?module=${module}&controller=${controller}&action=${action}&ajax=1`;
		for (const [key, value] of Object.entries(params)) {
			url += `&${key}=${value}`;
		}

		// 4. Hent indhold via AJAX
		fetch(url)
			.then((res) => res.text())
			.then((html) => {
				modalEl.querySelector('.modal-content').innerHTML = html;

				// --- VIGTIGT: Initialiser logik EFTER indholdet er sat ind ---

				// Kør Collection Form logik (dropdowns, widget, auto-add)
				// Vi tjekker nu bredere for handlinger, da 'form' er den nye standard for oprettelse
				if (
					(action === 'form' || action === 'add' || action === 'edit') &&
					typeof this.initDependentDropdowns === 'function'
				) {
					console.log(
						'App.openModal: Initializing Collection Form logic...',
					);
					this.initDependentDropdowns();
				}

				// Kør Media Upload logik
				if (
					action === 'media_step' &&
					typeof this.initMediaUploads === 'function'
				) {
					console.log('App.openModal: Initializing Media Upload logic...');
					this.initMediaUploads();
				}

				// Kør andre specifikke initialisere her hvis nødvendigt...
				// F.eks. Master Toy modal logik hvis den har sin egen init funktion
			})
			.catch((err) => {
				console.error('Modal Load Error:', err);
				modalEl.querySelector('.modal-content').innerHTML = `
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger m-0">Failed to load content: ${err}</div>
                    </div>
                `;
			});
	},
};

/**
 * GLOBAL TOAST HELPER
 */
App.showToast = function (message, type = 'success') {
	const toastEl = document.getElementById('liveToast');
	const toastBody = document.getElementById('toastBody');

	if (!toastEl || !toastBody) return;

	// S�t tekst
	toastBody.textContent = message;

	// H�ndter farver (hvis du vil have error toasts senere)
	toastEl.className = `toast align-items-center text-white border-0 bg-${type}`;

	// Vis med Bootstrap
	const toast = new bootstrap.Toast(toastEl);
	toast.show();
};
