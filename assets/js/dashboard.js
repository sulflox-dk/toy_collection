/**
 * Dashboard Logic
 * Nu meget simplere, da den bruger den delte CollectionForm logik
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log('Initializing Dashboard listeners...');

    // EDIT DATA BUTTON (Blyant ikon)
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-toy-btn');
        if (btn) {
            e.preventDefault();
            const id = btn.dataset.id;
            
            if (window.CollectionForm) {
                CollectionForm.openEditModal(id);
            } else {
                console.error('CollectionForm script is missing!');
            }
        }
    });

    // EDIT PHOTOS BUTTON (Kamera ikon)
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-photos-btn');
        if (btn) {
            e.preventDefault();
            const id = btn.dataset.id;

            if (window.CollectionForm) {
                CollectionForm.openMediaModal(id);
            } else {
                console.error('CollectionForm script is missing!');
            }
        }
    });
    
    // Vi overskriver success-håndteringen, så dashboardet blot reloader
    // for at vise de nye data i "Recently Added" eller statistikken
    if (window.CollectionForm) {
        window.CollectionForm.handleSaveSuccess = function(data) {
            App.showToast('Saved! Refreshing dashboard...');
            setTimeout(() => window.location.reload(), 800);
        };
    }
});