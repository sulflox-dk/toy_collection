const SubjectMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('subjectsGridContainer');
        this.filterType = document.getElementById('filterType');
        this.searchInput = document.getElementById('searchName');
        this.currentDeleteId = null;

        if(this.filterType) this.filterType.addEventListener('change', () => this.loadPage(1));
        
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
            module: 'Universe', controller: 'Subject', action: 'index',
            ajax_grid: 1, page: page,
            type: this.filterType.value,
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
        document.getElementById('createSubjectForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm('store', new FormData(e.target));
        });

        document.getElementById('editSubjectForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm('update', new FormData(e.target));
        });

        this.initDeleteModal();
        this.attachGridListeners();
    },

    attachGridListeners: function() {
        const editModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.closest('tr').dataset.json);
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_type').value = data.type;
                document.getElementById('edit_faction').value = data.faction;
                editModal.show();
            });
        });

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = JSON.parse(this.closest('tr').dataset.json);
                SubjectMgr.prepareDelete(data, deleteModal);
            });
        });
    },

    prepareDelete: function(data, modal) {
        this.currentDeleteId = data.id;
        document.getElementById('del_name').textContent = data.name;
        document.getElementById('del_usage_count').textContent = data.usage_count;
        
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2').classList.add('d-none');

        // Populate Dropdown
        const select = document.getElementById('migrateTargetSelect');
        select.innerHTML = '<option>Loading...</option>';
        select.disabled = true;

        fetch(`${this.baseUrl}?module=Universe&controller=Subject&action=get_all_simple`)
            .then(res => res.json())
            .then(items => {
                select.innerHTML = '';
                let count = 0;
                items.forEach(s => {
                    if (s.id != this.currentDeleteId) {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = `${s.name} (${s.type})`;
                        select.appendChild(opt);
                        count++;
                    }
                });
                if (count === 0) select.innerHTML = '<option value="">No other subjects found</option>';
                select.disabled = false;
            });
        
        modal.show();
    },
    
    initDeleteModal: function() {
        document.getElementById('btnDeleteSimple').addEventListener('click', () => this.executeDelete(this.currentDeleteId, null));
        
        document.getElementById('btnGoToStep2').addEventListener('click', () => {
            const select = document.getElementById('migrateTargetSelect');
            if (select.disabled || select.value === "") {
                alert('No other subjects available!'); return;
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
        fetch(`${this.baseUrl}?module=Universe&controller=Subject&action=${action}`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // 1. LUK MODAL (hvis det var edit)
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                // 2. RESET FORM (hvis det var create)
                if (action === 'store') {
                    const createForm = document.getElementById('createSubjectForm');
                    if(createForm) createForm.reset();
                    App.showToast('Subject created successfully!');
                } else if (action === 'update') {
                    App.showToast('Subject updated successfully!');
                }

                // 3. RELOAD GRID
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

        fetch(`${this.baseUrl}?module=Universe&controller=Subject&action=delete`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                // Vis besked og reload
                App.showToast('Subject deleted successfully!');
                this.loadPage(1);
            } else { alert(data.error); }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => SubjectMgr.init());