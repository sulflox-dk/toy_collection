const ManMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('manGridContainer');
        this.searchInput = document.getElementById('searchName');
        this.currentDeleteId = null;

        // Search Listener
        let timeout;
        if(this.searchInput) {
            this.searchInput.addEventListener('keyup', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.loadPage(1), 400);
            });
        }

        this.initForms();
    },

    loadPage: function(page) {
        const params = new URLSearchParams({
            module: 'Catalog', 
            controller: 'Manufacturer', 
            action: 'index',
            ajax_grid: 1, 
            page: page,
            search: this.searchInput.value
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

    initForms: function() {
        document.getElementById('createManForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm('store', new FormData(e.target));
        });

        document.getElementById('editManForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm('update', new FormData(e.target));
        });

        this.initDeleteModal();
        this.attachGridListeners();
    },

    attachGridListeners: function() {
        const editModal = new bootstrap.Modal(document.getElementById('editManModal'));
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.closest('tr').dataset.json);
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_show').checked = (data.show_on_dashboard == 1);
                editModal.show();
            });
        });

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteManModal'));
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.closest('tr').dataset.json);
                ManMgr.prepareDelete(data, deleteModal);
            });
        });
    },

    prepareDelete: function(data, modal) {
        this.currentDeleteId = data.id;
        document.getElementById('del_name').textContent = data.name;
        document.getElementById('del_usage_count').textContent = data.line_count;
        
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2').classList.add('d-none');

        // Populate Dropdown
        const select = document.getElementById('migrateTargetSelect');
        select.innerHTML = '<option>Loading...</option>';
        select.disabled = true;

        fetch(`${this.baseUrl}?module=Catalog&controller=Manufacturer&action=get_all_simple`)
            .then(res => res.json())
            .then(items => {
                select.innerHTML = '';
                let count = 0;
                items.forEach(s => {
                    if (s.id != this.currentDeleteId) {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        select.appendChild(opt);
                        count++;
                    }
                });
                if (count === 0) select.innerHTML = '<option value="">No other manufacturers found</option>';
                select.disabled = false;
            });
        
        modal.show();
    },
    
    initDeleteModal: function() {
        document.getElementById('btnDeleteSimple').addEventListener('click', () => this.executeDelete(this.currentDeleteId, null));
        
        document.getElementById('btnGoToStep2').addEventListener('click', () => {
            const select = document.getElementById('migrateTargetSelect');
            if (select.disabled || select.value === "") {
                alert('No other manufacturers available!'); return;
            }
            document.getElementById('step1').classList.add('d-none');
            document.getElementById('step2').classList.remove('d-none');
        });

        document.getElementById('btnBackToStep1').addEventListener('click', () => {
            document.getElementById('step2').classList.add('d-none');
            document.getElementById('step1').classList.remove('d-none');
        });

        document.getElementById('btnMigrateAndDelete').addEventListener('click', () => {
            const targetId = document.getElementById('migrateTargetSelect').value;
            if(targetId) this.executeDelete(this.currentDeleteId, targetId);
        });
    },

    submitForm: function(action, formData) {
        fetch(`${this.baseUrl}?module=Catalog&controller=Manufacturer&action=${action}`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                if (action === 'store') {
                    document.getElementById('createManForm').reset();
                    App.showToast('Manufacturer created successfully!');
                } else if (action === 'update') {
                    App.showToast('Manufacturer updated successfully!');
                }
                
                this.loadPage(1); // Reload grid via AJAX
            } else { 
                alert(data.error); 
            }
        });
    },

    executeDelete: function(id, migrateToId) {
        const formData = new FormData();
        formData.append('id', id);
        if (migrateToId) formData.append('migrate_to_id', migrateToId);

        fetch(`${this.baseUrl}?module=Catalog&controller=Manufacturer&action=delete`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                App.showToast('Manufacturer deleted successfully!');
                this.loadPage(1); // Reload grid
            } else { alert(data.error); }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => ManMgr.init());