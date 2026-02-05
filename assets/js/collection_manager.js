const CollectionMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('collectionGridContainer');
        
        // Element references
        this.search = document.getElementById('searchCollection');
        this.fUniverse = document.getElementById('filterUniverse');
        this.fLine = document.getElementById('filterLine');
        this.fEntSource = document.getElementById('filterEntSource');
        this.fStorage = document.getElementById('filterStorage');
        this.fSource = document.getElementById('filterPurchaseSource');
        this.fStatus = document.getElementById('filterStatus');

        this.attachFilterListeners();
        this.loadPage(1);
    },

    attachFilterListeners: function() {
        const filters = [
            this.fUniverse, this.fLine, this.fEntSource, 
            this.fStorage, this.fSource, this.fStatus
        ];

        filters.forEach(f => {
            if(f) f.addEventListener('change', () => this.loadPage(1));
        });

        let timeout;
        if(this.search) {
            this.search.addEventListener('keyup', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.loadPage(1), 400);
            });
        }
    },

    resetFilters: function() {
        if(this.search) this.search.value = '';
        const selects = document.querySelectorAll('.card-header select');
        selects.forEach(s => s.value = '');
        this.loadPage(1);
    },

    loadPage: function(page) {
        const params = new URLSearchParams({
            module: 'Collection', 
            controller: 'Toy', 
            action: 'index',
            ajax_grid: 1, 
            page: page,
            universe_id: this.fUniverse ? this.fUniverse.value : '',
            line_id: this.fLine ? this.fLine.value : '',
            ent_source_id: this.fEntSource ? this.fEntSource.value : '',
            storage_id: this.fStorage ? this.fStorage.value : '',
            source_id: this.fSource ? this.fSource.value : '',
            status: this.fStatus ? this.fStatus.value : '',
            search: this.search ? this.search.value : ''
        });

        this.container.style.opacity = '0.5';
        fetch(`${this.baseUrl}?${params.toString()}`)
            .then(res => res.text())
            .then(html => {
                this.container.innerHTML = html;
                this.container.style.opacity = '1';
                this.attachGridListeners(); 
            });
    },

    attachGridListeners: function() {
        // Edit Button -> Bruger CollectionForm (fra collection-form.js)
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                if(window.CollectionForm) {
                    CollectionForm.openEditModal(id);
                } else {
                    console.error('CollectionForm not loaded');
                }
            });
        });

        // Media Button -> Bruger App.initMediaUploads logikken
        document.querySelectorAll('.btn-media').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                // Vi genbruger samme logik som på dashboardet for media modal
                // Men vi har brug for en funktion der åbner den. 
                // Hvis CollectionForm ikke har en "openMedia", må vi bruge en direkte approach:
                App.openModal('Collection', 'Toy', 'modal_add_media', { id: id });
            });
        });
    }
};

// Vi hægter os på CollectionForm's success callback for at reloade listen
// Dette er et simpelt hack, da vi ikke har events.
document.addEventListener('DOMContentLoaded', () => {
    CollectionMgr.init();
    
    // Hvis CollectionForm findes, overskriv dens "onSuccess" midlertidigt eller lyt efter reload
    // En bedre måde: Modalen loader hele siden ved success?
    // I din nuværende app.js ser det ud til at 'modal_step2' reloader via loadPage(1).
    // Lad os antage at CollectionForm.saveToy() reloader siden eller vi skal injecte reload:
    
    const originalSave = window.CollectionForm ? window.CollectionForm.handleSaveSuccess : null;
    if (window.CollectionForm) {
        window.CollectionForm.handleSaveSuccess = function(data) {
            // Kald originalen hvis den findes
            if (originalSave) originalSave(data);
            // OG reload vores liste
            CollectionMgr.loadPage(1);
        };
    }
});