const MediaLib = {
    isBulkMode: false,
    selectedIds: new Set(),
    
    init: function() {
        this.container = document.getElementById('mediaGridContainer');
        this.filterConn = document.getElementById('filterConnection');
        this.filterTag = document.getElementById('filterTag');
        this.search = document.getElementById('mediaSearch');
        
        // Listeners
        this.filterConn.addEventListener('change', () => this.loadPage(1));
        this.filterTag.addEventListener('change', () => this.loadPage(1));
        
        // Search debounce
        let timeout;
        this.search.addEventListener('keyup', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => this.loadPage(1), 500);
        });

        // Bulk Actions
        document.getElementById('btnBulkSelect').addEventListener('click', () => this.toggleBulkMode());
        document.getElementById('btnBulkDelete').addEventListener('click', () => this.deleteSelected());

        // Load initial data
        this.loadPage(1);
    },

    loadPage: function(page) {
        // Byg URL parametre
        const params = new URLSearchParams({
            module: 'Media',
            controller: 'MediaLibrary',
            action: 'index',
            ajax_grid: 1,
            page: page,
            filter_connection: this.filterConn.value,
            filter_tag: this.filterTag.value,
            search: this.search.value
        });

        // Vis loader hvis det tager tid
        this.container.style.opacity = '0.5';

        fetch(`${App.baseUrl}?${params.toString()}`)
            .then(res => res.text())
            .then(html => {
                this.container.innerHTML = html;
                this.container.style.opacity = '1';
                // Gendan bulk state hvis vi bladrer i bulk mode
                if (this.isBulkMode) {
                    this.container.querySelectorAll('.media-item').forEach(el => {
                        const id = el.dataset.id;
                        if (this.selectedIds.has(id)) {
                            el.classList.add('selected');
                            el.querySelector('input').checked = true;
                        }
                    });
                }
            });
    },

    toggleBulkMode: function() {
        this.isBulkMode = !this.isBulkMode;
        const btn = document.getElementById('btnBulkSelect');
        const grid = document.querySelector('.media-grid'); // Kan være null hvis grid er tomt
        
        if (this.isBulkMode) {
            btn.textContent = 'Cancel Selection';
            btn.classList.replace('btn-outline-secondary', 'btn-secondary');
            document.body.classList.add('bulk-select-active');
        } else {
            btn.textContent = 'Bulk Select';
            btn.classList.replace('btn-secondary', 'btn-outline-secondary');
            document.body.classList.remove('bulk-select-active');
            this.selectedIds.clear();
            this.updateBulkUI();
            // Fjern markeringer
            document.querySelectorAll('.media-item.selected').forEach(el => {
                el.classList.remove('selected');
                el.querySelector('input').checked = false;
            });
        }
    },

    onItemClick: function(element, event) {
        if (event.target.type === 'checkbox') {
            this.toggleSelection(element, event.target.checked);
            return;
        }

        if (this.isBulkMode) {
            const checkbox = element.querySelector('input');
            checkbox.checked = !checkbox.checked;
            this.toggleSelection(element, checkbox.checked);
        } else {
            // NORMAL MODE: Åbn modal via App helperen
            const id = element.dataset.id;
            App.openModal('Media', 'MediaLibrary', 'details', {id: id});
        }
    },

    toggleSelection: function(element, isChecked) {
        const id = element.dataset.id;
        if (isChecked) {
            element.classList.add('selected');
            this.selectedIds.add(id);
        } else {
            element.classList.remove('selected');
            this.selectedIds.delete(id);
        }
        this.updateBulkUI();
    },

    updateBulkUI: function() {
        const delBtn = document.getElementById('btnBulkDelete');
        const countSpan = document.getElementById('selectedCount');
        countSpan.textContent = this.selectedIds.size;
        
        if (this.selectedIds.size > 0) {
            delBtn.classList.remove('d-none');
        } else {
            delBtn.classList.add('d-none');
        }
    },

    deleteSelected: function() {
        if (!confirm(`Are you sure you want to delete ${this.selectedIds.size} images? This cannot be undone.`)) return;

        fetch(`${App.baseUrl}?module=Media&controller=MediaLibrary&action=delete_bulk`, {
            method: 'POST',
            body: JSON.stringify({ ids: Array.from(this.selectedIds) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.selectedIds.clear();
                this.updateBulkUI();
                this.loadPage(1); // Reload grid
            } else {
                alert('Error: ' + data.error);
            }
        });
    },

    unlinkConnection: function(mediaId, context, targetId, btn) {
        if(!confirm('Remove this image from the item? The image file will remain in the library.')) return;

        const formData = new FormData();
        formData.append('media_id', mediaId);
        formData.append('target_id', targetId);
        formData.append('context', context);

        fetch(`${App.baseUrl}?module=Media&controller=MediaLibrary&action=unlink`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Fjern rækken fra listen visuelt
                const li = btn.closest('li');
                li.remove();
                
                // Tjek om listen er tom
                const list = document.getElementById('connectionList');
                if (list && list.children.length === 0) {
                    list.parentElement.innerHTML = '<div class="alert alert-warning py-2 small">No connections left.</div>';
                }
            } else {
                alert('Error: ' + data.error);
            }
        });
    },

    // NY: Slet fil fra modalen
    deleteSingle: function(mediaId) {
        if (!confirm('Are you sure you want to permanently delete this file? This will remove it from ALL connected items.')) return;

        fetch(`${App.baseUrl}?module=Media&controller=Media&action=delete&id=${mediaId}`, { // Bruger MediaController (ikke Library)
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Luk modal og opdater grid
                const modalEl = document.getElementById('appModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                this.loadPage(1); // Refresh grid
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        });
    }
};

// Start
document.addEventListener('DOMContentLoaded', () => MediaLib.init());