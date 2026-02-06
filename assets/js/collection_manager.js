const CollectionMgr = {
    init: function() {
        this.baseUrl = App.baseUrl; // Sikrer at vi har base URL
        this.container = document.getElementById('collectionGridContainer');
        
        // Element references
        this.search = document.getElementById('searchCollection');
        this.fUniverse = document.getElementById('filterUniverse');
        this.fLine = document.getElementById('filterLine');
        this.fEntSource = document.getElementById('filterEntSource');
        this.fStorage = document.getElementById('filterStorage');
        this.fSource = document.getElementById('filterPurchaseSource');
        this.fStatus = document.getElementById('filterStatus');

        // 1. Start lyttere til filtre
        this.attachFilterListeners();

        // 2. Start "Global" lytter til grid-knapper (Event Delegation)
        // Dette erstatter den gamle attachGridListeners og virker altid!
        if (this.container) {
            this.container.addEventListener('click', (e) => {
                // SLET KNAP
                const delBtn = e.target.closest('.btn-delete');
                if (delBtn) {
                    this.handleDelete(delBtn);
                    return; // Stop her
                }

                // EDIT KNAP
                const editBtn = e.target.closest('.btn-edit');
                if (editBtn) {
                    const id = editBtn.closest('tr').dataset.id;
                    if(window.CollectionForm) CollectionForm.openEditModal(id);
                    return;
                }

                // MEDIA KNAP
                const mediaBtn = e.target.closest('.btn-media');
                if (mediaBtn) {
                    const id = mediaBtn.closest('tr').dataset.id;
                    if(window.CollectionForm) CollectionForm.openMediaModal(id);
                    return;
                }
            });
        }

        const match = document.cookie.match(new RegExp('(^| )collection_view_mode=([^;]+)'));
        const currentMode = match ? match[2] : 'list';
        this.updateViewButtons(currentMode);

        // Load første side ved start
        this.loadPage(1);
    },

    // Ny separat funktion til sletning
    handleDelete: function(btn) {
        if(!confirm('Are you sure? This will delete the toy, all its items, and ALL associated photos permanently.')) {
            return;
        }

        const tr = btn.closest('tr');
        const id = tr.dataset.id;
        
        // Visuel feedback (gør rækken utydelig)
        tr.style.opacity = '0.3';

        fetch(`${this.baseUrl}?module=Collection&controller=Toy&action=delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                App.showToast('Item deleted successfully!');
                this.loadPage(1); // Genindlæs listen
            } else {
                tr.style.opacity = '1'; 
                alert('Error deleting: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            tr.style.opacity = '1';
            console.error(err);
            alert('System error occurred.');
        });
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
        this.currentPage = page;
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
                    // Vi behøver ikke kalde attachGridListeners længere, 
                    // da "init" lytteren fanger alt!
                });
        }
    },

    // Skift view mode (list/cards)
    switchView: function(mode) {
        // 1. Gem i cookie (gælder i 1 år)
        document.cookie = "collection_view_mode=" + mode + "; path=/; max-age=31536000";
        
        // 2. Opdater knap-udseende med det samme
        this.updateViewButtons(mode);

        // 3. Genindlæs listen for at få den nye HTML fra serveren
        // Vi bruger currentPage, som vi gemte sidst loadPage kørte (se rettelse i loadPage herunder)
        this.loadPage(this.currentPage || 1);
    },

    updateViewButtons: function(mode) {
        const btnList = document.getElementById('btn-view-list');
        const btnCards = document.getElementById('btn-view-cards');
        
        if(btnList && btnCards) {
            if(mode === 'list') {
                btnList.classList.add('active', 'bg-secondary', 'text-white');
                btnCards.classList.remove('active', 'bg-secondary', 'text-white');
            } else {
                btnCards.classList.add('active', 'bg-secondary', 'text-white');
                btnList.classList.remove('active', 'bg-secondary', 'text-white');
            }
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    CollectionMgr.init();
    
    // Hack til reload efter save
    if (window.CollectionForm) {
        window.CollectionForm.handleSaveSuccess = function(data) {
            App.showToast('Saved successfully!');
            CollectionMgr.loadPage(1); 
        };
    }
});