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

			// Hent Edit Modal HTML
			fetch(
				`${App.baseUrl}?module=Collection&controller=Toy&action=edit&id=${id}`,
			)
				.then((res) => res.text())
				.then((html) => {
					const modalEl = document.getElementById('addToyModal');
					const modalBody = modalEl.querySelector('.modal-content');
					modalBody.innerHTML = html;

					// Vis modal
					const modal = new bootstrap.Modal(modalEl);
					modal.show();

					// Genaktiver dropdown logik fra collection.js
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

			// Hent Media Modal HTML
			fetch(
				`${App.baseUrl}?module=Collection&controller=Toy&action=media_step&id=${id}`,
			)
				.then((res) => res.text())
				.then((html) => {
					const modalEl = document.getElementById('addToyModal');
					const modalBody = modalEl.querySelector('.modal-content');
					modalBody.innerHTML = html;

					const modal = new bootstrap.Modal(modalEl);
					modal.show();

					// Genaktiver upload logik fra collection.js
					if (typeof App.initMediaUploads === 'function') {
						App.initMediaUploads();
					}
				})
				.catch((err) => console.error('Error loading media form:', err));
		}
	});
});
