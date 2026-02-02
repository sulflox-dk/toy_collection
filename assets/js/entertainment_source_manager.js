const SourceMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('sourcesGridContainer');
        this.currentDeleteId = null;
        
        // Inputs
        this.filterUniverse = document.getElementById('filterUniverse');
        this.filterType = document.getElementById('filterType');
        this.searchInput = document.getElementById('searchName');

        // Listeners for Filtering
        if(this.filterUniverse) this.filterUniverse.addEventListener('change', () => this.loadPage(1));
        if(this.filterType) this.filterType.addEventListener('change', () => this.loadPage(1));
        
        let timeout;
        if(this.searchInput) {
            this.searchInput.addEventListener('keyup', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.loadPage(1), 400);
            });
        }

        // Init Create/Edit/Delete Logic
        this.initForms();
    },

    loadPage: function(page) {
        const params = new URLSearchParams({
            module: 'Universe',
            controller: 'EntertainmentSource',
            action: 'index',
            ajax_grid: 1,
            page: page,
            universe_id: this.filterUniverse.value,
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
        // Create
        const createForm = document.getElementById('createSourceForm');
        if(createForm) {
            createForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm('store', new FormData(e.target));
            });
        }

        // Edit Modal Form
        const editForm = document.getElementById('editSourceForm');
        if(editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm('update', new FormData(e.target));
            });
        }

        this.initDeleteModal();
        this.attachGridListeners(); // Bind første gang
    },

    attachGridListeners: function() {
        // Edit Buttons
        const editModalEl = document.getElementById('editSourceModal');
        if(editModalEl) {
            const editModal = new bootstrap.Modal(editModalEl);
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.closest('tr').dataset.json);
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_universe_id').value = data.universe_id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_type').value = data.type;
                    document.getElementById('edit_year').value = data.release_year;
                    editModal.show();
                });
            });
        }

        // Delete Buttons
        const deleteModalEl = document.getElementById('deleteSourceModal');
        if(deleteModalEl) {
            const deleteModal = new bootstrap.Modal(deleteModalEl);
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.closest('tr').dataset.json);
                    SourceMgr.prepareDelete(data, deleteModal);
                });
            });
        }
    },

    prepareDelete: function(data, modal) {
        this.currentDeleteId = data.id;
        document.getElementById('del_name').textContent = data.name;
        document.getElementById('del_usage_count').textContent = data.toy_count;
        
        // Reset UI
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2').classList.add('d-none');

        // Populate Dropdown via API
        const select = document.getElementById('migrateTargetSelect');
        select.innerHTML = '<option>Loading...</option>';
        select.disabled = true;

        fetch(`${this.baseUrl}?module=Universe&controller=EntertainmentSource&action=get_all_simple`)
            .then(res => res.json())
            .then(sources => {
                select.innerHTML = '';
                let count = 0;
                sources.forEach(s => {
                    // Vis ikke den vi er ved at slette
                    if (s.id != this.currentDeleteId) {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = `${s.name} (${s.universe_name})`;
                        select.appendChild(opt);
                        count++;
                    }
                });
                
                if (count === 0) {
                    select.innerHTML = '<option value="">No other sources found</option>';
                }
                select.disabled = false;
            })
            .catch(err => {
                select.innerHTML = '<option>Error loading sources</option>';
                console.error(err);
            });
        
        modal.show();
    },
    
    initDeleteModal: function() {
        // Step 1: Delete Simple
        const btnSimple = document.getElementById('btnDeleteSimple');
        if(btnSimple) {
            btnSimple.addEventListener('click', () => this.executeDelete(this.currentDeleteId, null));
        }
        
        // Step 1 -> Step 2
        const btnGo2 = document.getElementById('btnGoToStep2');
        if(btnGo2) {
            btnGo2.addEventListener('click', () => {
                const select = document.getElementById('migrateTargetSelect');
                // Tjek om dropdownen er tom (eller kun har 'loading'/'no sources')
                if (select.disabled || select.value === "") {
                    alert('No other sources available to migrate to!');
                    return;
                }
                document.getElementById('step1').classList.add('d-none');
                document.getElementById('step2').classList.remove('d-none');
            });
        }

        // Step 2 -> Step 1 (Back)
        const btnBack = document.getElementById('btnBackToStep1');
        if(btnBack) {
            btnBack.addEventListener('click', () => {
                document.getElementById('step2').classList.add('d-none');
                document.getElementById('step1').classList.remove('d-none');
            });
        }

        // Step 2: Migrate & Delete
        const btnMigrate = document.getElementById('btnMigrateAndDelete');
        if(btnMigrate) {
            btnMigrate.addEventListener('click', () => {
                const targetId = document.getElementById('migrateTargetSelect').value;
                if(targetId) this.executeDelete(this.currentDeleteId, targetId);
            });
        }
    },

    submitForm: function(action, formData) {
        fetch(`${this.baseUrl}?module=Universe&controller=EntertainmentSource&action=${action}`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                // RESET FORM
                if (action === 'store') {
                    const createForm = document.getElementById('createSourceForm');
                    if(createForm) createForm.reset();
                    App.showToast('Source created successfully!');
                } else if (action === 'update') {
                    App.showToast('Source updated successfully!');
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

        fetch(`${this.baseUrl}?module=Universe&controller=EntertainmentSource&action=delete`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const openModal = document.querySelector('.modal.show');
                if(openModal) bootstrap.Modal.getInstance(openModal).hide();
                
                // Vis besked og reload
                App.showToast('Source deleted successfully!');
                this.loadPage(1);
            } else {
                alert(data.error);
            }
        });
    }
};

// Start
document.addEventListener('DOMContentLoaded', () => SourceMgr.init());