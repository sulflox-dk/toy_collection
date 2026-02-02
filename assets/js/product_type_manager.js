const TypeMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('typeGridContainer');
        this.searchInput = document.getElementById('searchName');
        this.currentDeleteId = null;

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
            controller: 'ProductType', 
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
        document.getElementById('createTypeForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm('store', new FormData(e.target));
        });

        document.getElementById('editTypeForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm('update', new FormData(e.target));
        });

        this.initDeleteModal();
        this.attachGridListeners();
    },

    attachGridListeners: function() {
        const editModal = new bootstrap.Modal(document.getElementById('editTypeModal'));
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.closest('tr').dataset.json);
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_name').value = data.type_name;
                editModal.show();
            });
        });

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteTypeModal'));
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.closest('tr').dataset.json);
                TypeMgr.prepareDelete(data, deleteModal);
            });
        });
    },

    prepareDelete: function(data, modal) {
        this.currentDeleteId = data.id;
        document.getElementById('del_name').textContent = data.type_name;
        document.getElementById('del_usage_count').textContent = data.toy_count;
        
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2').classList.add('d-none');

        const select = document.getElementById('migrateTargetSelect');
        select.innerHTML = '<option>Loading...</option>';
        select.disabled = true;

        fetch(`${this.baseUrl}?module=Catalog&controller=ProductType&action=get_all_simple`)
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
                if (count === 0) select.innerHTML = '<option value="">No other types found</option>';
                select.disabled = false;
            });
        
        modal.show();
    },
    
    initDeleteModal: function() {
        document.getElementById('btnDeleteSimple').addEventListener('click', () => this.executeDelete(this.currentDeleteId, null));
        
        document.getElementById('btnGoToStep2').addEventListener('click', () => {
            const select = document.getElementById('migrateTargetSelect');
            if (select.disabled || select.value === "") {
                alert('No other types available!'); return;
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
        fetch(`${this.baseUrl}?module=Catalog&controller=ProductType&action=${action}`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                if (action === 'store') {
                    document.getElementById('createTypeForm').reset();
                    App.showToast('Product Type created successfully!');
                } else if (action === 'update') {
                    App.showToast('Product Type updated successfully!');
                }
                
                this.loadPage(1);
            } else { 
                alert(data.error); 
            }
        });
    },

    executeDelete: function(id, migrateToId) {
        const formData = new FormData();
        formData.append('id', id);
        if (migrateToId) formData.append('migrate_to_id', migrateToId);

        fetch(`${this.baseUrl}?module=Catalog&controller=ProductType&action=delete`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                App.showToast('Product Type deleted successfully!');
                this.loadPage(1);
            } else { alert(data.error); }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => TypeMgr.init());