const App = {
	// Tjek at URL'en passer til din installation
	baseUrl: typeof SITE_URL !== 'undefined' ? SITE_URL : '/',

	/**
	 * √Öbner den globale modal og henter indhold
	 */
	openModal: function (module, controller, action, params = {}) {
		const modalEl = document.getElementById('appModal');

		// 1. FIX: Tjek om modalen allerede findes/er √•ben
		let modal = bootstrap.Modal.getInstance(modalEl);

		if (!modal) {
			// Hvis den ikke findes endnu, opret den
			modal = new bootstrap.Modal(modalEl);
			modal.show();
		} else {
			// Hvis den findes, men er lukket, s√• vis den.
			// Hvis den allerede ER √•ben (fordi vi skifter trin), s√• g√∏r vi ingenting her (ingen ekstra backdrop)
			if (!modalEl.classList.contains('show')) {
				modal.show();
			}
		}

		// 2. Vis loader mens vi henter det nye indhold
		// Vi overskriver kun indholdet, vi lukker/√•bner ikke selve modal-vinduet
		modalEl.querySelector('.modal-content').innerHTML = `
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-secondary" role="status"></div>
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

                // RETTET: Tjek ogsÂ for 'add' og 'edit' handlingerne
                if (
                    (action === 'form' || action === 'add' || action === 'edit') &&
                    typeof this.initDependentDropdowns === 'function'
                ) {
                    this.initDependentDropdowns();
                }
                
                // Tilf¯j evt. ogsÂ dette, hvis du vil vÊre sikker pÂ Media uploads virker i 'media_step'
                if (
                    action === 'media_step' && 
                    typeof this.initMediaUploads === 'function'
                ) {
                    this.initMediaUploads();
                }
            })
			.catch((err) => {
				modalEl.querySelector('.modal-body').innerHTML =
					`<div class="alert alert-danger">Error: ${err}</div>`;
			});
	},
};

/**
 * GLOBAL TOAST HELPER
 */
App.showToast = function(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toastBody');
    
    if (!toastEl || !toastBody) return;

    // SÊt tekst
    toastBody.textContent = message;
    
    // HÂndter farver (hvis du vil have error toasts senere)
    toastEl.className = `toast align-items-center text-white border-0 bg-${type}`;

    // Vis med Bootstrap
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
};