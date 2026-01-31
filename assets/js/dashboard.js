/**
 * Dashboard Specific Logic
 */
document.addEventListener('DOMContentLoaded', function () {
	console.log('Initializing Dashboard listeners...');

	// EDIT DATA BUTTON
	document.body.addEventListener('click', function (e) {
		const btn = e.target.closest('.edit-toy-btn');
		if (btn) {
			e.preventDefault();
			const id = btn.dataset.id;

			// RETTET: Vi bruger 'appModal' (den globale fra main.php)
			const modalEl = document.getElementById('appModal');

			// Optional: Vis spinner mens vi venter (god UX)
			if (modalEl.querySelector('.modal-body')) {
				modalEl.querySelector('.modal-body').innerHTML =
					'<div class="text-center py-5"><div class="spinner-border"></div></div>';
			}

			fetch(
				`${App.baseUrl}?module=Collection&controller=Toy&action=edit&id=${id}`,
			)
				.then((res) => res.text())
				.then((html) => {
					// Vi opdaterer indholdet i modal-content
					const modalContent = modalEl.querySelector('.modal-content');
					modalContent.innerHTML = html;

					const modal = new bootstrap.Modal(modalEl);
					modal.show();

					if (typeof App.initDependentDropdowns === 'function') {
						App.initDependentDropdowns();
					}
				})
				.catch((err) => console.error('Error loading edit form:', err));
		}
	});

	// EDIT PHOTOS BUTTON
	document.body.addEventListener('click', function (e) {
		const btn = e.target.closest('.edit-photos-btn');
		if (btn) {
			e.preventDefault();
			const id = btn.dataset.id;

			// RETTET: Vi bruger 'appModal'
			const modalEl = document.getElementById('appModal');

			fetch(
				`${App.baseUrl}?module=Collection&controller=Toy&action=media_step&id=${id}`,
			)
				.then((res) => res.text())
				.then((html) => {
					const modalContent = modalEl.querySelector('.modal-content');
					modalContent.innerHTML = html;

					const modal = new bootstrap.Modal(modalEl);
					modal.show();

					if (typeof App.initMediaUploads === 'function') {
						App.initMediaUploads();
					}
				})
				.catch((err) => console.error('Error loading media form:', err));
		}
	});
});
