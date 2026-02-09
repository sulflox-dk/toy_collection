/**
 * Dashboard Logic
 */
const DashboardMgr = {
	// Skift visning og reload siden
	switchView: function (mode) {
		// S�t cookie (samme navn som i CollectionMgr, s� valget huskes p� tv�rs af sider)
		document.cookie =
			'collection_view_mode=' + mode + '; path=/; max-age=31536000';

		// Reload for at controlleren kan rendere den nye visning
		window.location.reload();
	},

	init: function () {
		// S�t aktiv knap baseret p� cookie
		const match = document.cookie.match(
			new RegExp('(^| )collection_view_mode=([^;]+)'),
		);
		const currentMode = match ? match[2] : 'list';

		const btnList = document.getElementById('dash-btn-list');
		const btnCards = document.getElementById('dash-btn-cards');

		if (btnList && btnCards) {
			if (currentMode === 'list') {
				btnList.classList.add('active', 'bg-secondary', 'text-white');
			} else {
				btnCards.classList.add('active', 'bg-secondary', 'text-white');
			}
		}
	},
};

document.addEventListener('DOMContentLoaded', function () {
	console.log('Initializing Dashboard...');
	DashboardMgr.init();

	// Event listeners til edit/photo knapper (som vi lavede tidligere)
	// Vi bruger event delegation på body for at fange klik på dynamiske elementer
	document.body.addEventListener('click', function (e) {
		const btnDelete = e.target.closest('.delete-toy-btn');
		if (btnDelete) {
			e.preventDefault();
			// Brug CollectionMgr til sletning hvis muligt, ellers App.deleteToyItem
			if (
				window.CollectionMgr &&
				typeof CollectionMgr.handleDelete === 'function'
			) {
				CollectionMgr.handleDelete(btnDelete);
			} else if (typeof App.deleteToyItem === 'function') {
				// Fallback til core funktion hvis Manager ikke er fuldt loadet
				const id = btnDelete.closest('[data-id]').dataset.id;
				App.deleteToyItem(id, btnDelete);
			}
			return;
		}

		const btnEdit =
			e.target.closest('.edit-toy-btn') || e.target.closest('.btn-edit');
		if (btnEdit) {
			e.preventDefault();
			// Understøt både tr og card (data-id)
			const container = btnEdit.closest('[data-id]');
			if (container && window.CollectionForm) {
				CollectionForm.openEditModal(container.dataset.id);
			}
		}

		const btnPhoto =
			e.target.closest('.edit-photos-btn') || e.target.closest('.btn-media');
		if (btnPhoto) {
			e.preventDefault();
			const container = btnPhoto.closest('[data-id]');
			if (container && window.CollectionForm) {
				CollectionForm.openMediaModal(container.dataset.id);
			}
		}
	});

	// --- SLET NEDENSTÅENDE BLOK ---
	// Denne blok overskriver den smarte 'handleSaveSuccess' fra collection-form.js
	// og tvinger siden til at reloade. Nu hvor collection-form.js er universel,
	// skal vi fjerne denne forhindring.

	/*
    if (window.CollectionForm) {
        window.CollectionForm.handleSaveSuccess = function(data) {
            App.showToast('Saved! Refreshing dashboard...');
            setTimeout(() => window.location.reload(), 500);
        };
    }
    */
	// -----------------------------
});
