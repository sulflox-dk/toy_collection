/**
 * Dashboard Logic
 */
const DashboardMgr = {
    // Skift visning og reload siden
    switchView: function(mode) {
        // Sæt cookie (samme navn som i CollectionMgr, så valget huskes på tværs af sider)
        document.cookie = "collection_view_mode=" + mode + "; path=/; max-age=31536000";
        
        // Reload for at controlleren kan rendere den nye visning
        window.location.reload();
    },

    init: function() {
        // Sæt aktiv knap baseret på cookie
        const match = document.cookie.match(new RegExp('(^| )collection_view_mode=([^;]+)'));
        const currentMode = match ? match[2] : 'list';
        
        const btnList = document.getElementById('dash-btn-list');
        const btnCards = document.getElementById('dash-btn-cards');

        if(btnList && btnCards) {
            if(currentMode === 'list') {
                btnList.classList.add('active', 'bg-secondary', 'text-white');
            } else {
                btnCards.classList.add('active', 'bg-secondary', 'text-white');
            }
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    console.log('Initializing Dashboard...');
    DashboardMgr.init();

    // Event listeners til edit/photo knapper (som vi lavede tidligere)
    // De bruger window.CollectionForm som er loadet før denne fil
    document.body.addEventListener('click', function (e) {
        const btnDelete = e.target.closest('.btn-delete');
        if (btnDelete) {
            e.preventDefault();
            // Vi kan genbruge CollectionMgr's slette-logik, hvis den er tilgængelig
            // (CollectionMgr er loadet via collection-core.js/collection_manager.js)
            if (window.CollectionMgr && typeof CollectionMgr.handleDelete === 'function') {
                CollectionMgr.handleDelete(btnDelete);
            } else {
                console.error("CollectionMgr not found for delete action");
            }
            return; // Stop her
        }
        
        const btnEdit = e.target.closest('.edit-toy-btn') || e.target.closest('.btn-edit');
        if (btnEdit) {
            e.preventDefault();
            // Understøt både tr og card (data-id)
            const container = btnEdit.closest('[data-id]');
            if (container && window.CollectionForm) {
                CollectionForm.openEditModal(container.dataset.id);
            }
        }

        const btnPhoto = e.target.closest('.edit-photos-btn') || e.target.closest('.btn-media');
        if (btnPhoto) {
            e.preventDefault();
            const container = btnPhoto.closest('[data-id]');
            if (container && window.CollectionForm) {
                CollectionForm.openMediaModal(container.dataset.id);
            }
        }
    });

    // Success handler
    if (window.CollectionForm) {
        window.CollectionForm.handleSaveSuccess = function(data) {
            App.showToast('Saved! Refreshing dashboard...');
            setTimeout(() => window.location.reload(), 800);
        };
    }
});