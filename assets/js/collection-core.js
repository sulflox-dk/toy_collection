/**
 * COLLECTION CORE LOGIC
 * Denne fil udvider det globale App objekt defineret i app.js
 * Den håndterer sletning af items og initiering af lyttere.
 */

// Vi tjekker først om App findes (det bør den gøre fra app.js)
if (typeof App === 'undefined') {
	console.error(
		'Critical Error: app.js is not loaded. Collection Core cannot run.',
	);
} else {
	// Vi sætter placeholders hvis de ikke findes, men vi overskriver IKKE hele objektet
	if (typeof App.initDependentDropdowns === 'undefined')
		App.initDependentDropdowns = null;
	if (typeof App.initMediaUploads === 'undefined') App.initMediaUploads = null;

	// Global Slet Funktion (Bruges på Dashboardet)
	App.deleteToyItem = function (itemId, btnElement) {
		if (
			!confirm(
				'Are you sure you want to remove this item from your collection?',
			)
		)
			return;

		fetch(
			`${App.baseUrl}?module=Collection&controller=Api&action=delete_item&id=${itemId}`,
			{ method: 'POST' },
		)
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					const row = btnElement.closest('.child-item-row'); // Hvis inde i modal (form)

					if (row) {
						// Sletning inde fra modalen
						row.style.opacity = '0';
						setTimeout(() => row.remove(), 300);
					} else {
						// Sletning fra Dashboard / Grid
						// Prøv Smart Refresh hvis muligt
						const card = document.querySelector(`[data-id="${itemId}"]`);
						if (card && typeof CollectionMgr !== 'undefined') {
							// Vi fjerner kortet visuelt med det samme eller reloader grid
							card.style.opacity = '0';
							setTimeout(() => {
								if (CollectionMgr.loadPage) {
									CollectionMgr.loadPage(
										CollectionMgr.currentPage || 1,
									);
								} else {
									window.location.reload();
								}
							}, 300);
						} else {
							// Fallback til full reload
							window.location.reload();
						}
					}
				} else {
					alert('Error: ' + (data.error || 'Unknown error'));
				}
			})
			.catch((err) => {
				console.error('Delete failed', err);
				alert('An error occurred while deleting.');
			});
	};

	// Initialisering når siden er klar
	document.addEventListener('DOMContentLoaded', function () {
		// Start dependent dropdowns hvis funktionen findes (fra collection-form.js)
		if (typeof App.initDependentDropdowns === 'function') {
			App.initDependentDropdowns();
		}
	});
}
