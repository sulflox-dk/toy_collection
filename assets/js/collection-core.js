/**
 * CORE LOGIC
 * Setup af App namespace og globale funktioner
 */
const App = {
    baseUrl: window.AppConfig ? window.AppConfig.baseUrl : '', // Henter fra din PHP config hvis du har det, ellers hardcode
    // Placeholder objekter
    initDependentDropdowns: null, 
    initMediaUploads: null
};

// Global Slet Funktion (Bruges på Dashboardet)
App.deleteToyItem = function (itemId, btnElement) {
    if (!confirm('Are you sure you want to remove this item from your collection?')) return;

    fetch(`${App.baseUrl}?module=Collection&controller=Api&action=delete_item&id=${itemId}`, { method: 'POST' })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                const row = btnElement.closest('.child-item-row'); // Hvis inde i modal
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                } else {
                    // Hvis på dashboard
                    window.location.reload(); 
                }
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        });
};

// Initialisering når siden er klar
document.addEventListener('DOMContentLoaded', function() {
    if (typeof App.initDependentDropdowns === 'function') {
        App.initDependentDropdowns();
    }
    if (typeof App.initMediaUploads === 'function') {
        App.initMediaUploads();
    }
});