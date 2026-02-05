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
        // Load første side ved start
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

        if(this.container) {
            this.container.style.opacity = '0.5';
            fetch(`${this.baseUrl}?${params.toString()}`)
                .then(res => res.text())
                .then(html => {
                    this.container.innerHTML = html;
                    this.container.style.opacity = '1';
                    this.attachGridListeners(); 
                });
        }
    },

    attachGridListeners: function() {
        // Rediger knap (Blyant) - Bruger nu CollectionForm
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                if(window.CollectionForm) {
                    CollectionForm.openEditModal(id);
                }
            });
        });

        // Media knap (Kamera) - Bruger nu CollectionForm
        document.querySelectorAll('.btn-media').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                if(window.CollectionForm) {
                    CollectionForm.openMediaModal(id);
                }
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    CollectionMgr.init();
    
    // Her "hacker" vi CollectionForm til kun at reloade griddet i stedet for hele siden
    if (window.CollectionForm) {
        window.CollectionForm.handleSaveSuccess = function(data) {
            App.showToast('Saved successfully!');
            // Reload kun listen, ikke hele siden
            CollectionMgr.loadPage(1); 
            
            // Hvis modalen stadig er åben (ved fejl eller lignende), luk den evt.
            // Men typisk håndterer App.js lukningen eller reload af modal-indhold
        };
    }
});