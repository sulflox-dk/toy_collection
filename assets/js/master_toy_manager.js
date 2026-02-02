const MasterToyMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('masterToyGridContainer');
        this.search = document.getElementById('searchName');
        
        this.filterUni = document.getElementById('filterUniverse');
        this.filterLine = document.getElementById('filterLine');
        
        // RETTET: Ny reference
        this.filterSource = document.getElementById('filterSource');

        // Event Listeners
        // RETTET: Inkluder filterSource i arrayet
        const filters = [this.filterUni, this.filterLine, this.filterSource];
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

        this.attachGridListeners();
    },

    loadPage: function(page) {
        const params = new URLSearchParams({
            module: 'Catalog', controller: 'MasterToy', action: 'index',
            ajax_grid: 1, page: page,
            universe_id: this.filterUni.value,
            line_id: this.filterLine.value,
            
            // RETTET: Send source_id
            source_id: this.filterSource.value,
            
            search: this.search.value
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
        // DELETE
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                if(confirm('Delete this Master Toy? This cannot be undone.')) {
                    MasterToyMgr.executeDelete(id);
                }
            });
        });

        // EDIT DATA (Placeholder til næste trin)
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                alert('Edit form kommer i næste trin for ID: ' + id);
            });
        });

        // MEDIA (Placeholder til næste trin)
        document.querySelectorAll('.btn-media').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                alert('Media manager kommer i næste trin for ID: ' + id);
            });
        });
    },

    openUniverseSelect: function() {
        alert('Step 1 (Select Universe) kommer i næste trin');
    },

    executeDelete: function(id) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch(`${this.baseUrl}?module=Catalog&controller=MasterToy&action=delete`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                App.showToast('Toy deleted successfully!');
                this.loadPage(1);
            } else {
                alert(data.error);
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => MasterToyMgr.init());