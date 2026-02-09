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
        // Rediger knap (Blyant-ikonet i rækken)
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.closest('tr').dataset.id;
                if(window.CollectionForm && typeof CollectionForm.openEditModal === 'function') {
                    CollectionForm.openEditModal(id);
                } else {
                    console.error('CollectionForm.openEditModal er ikke tilgængelig');
                }
            });
        });

        // Media knap (Kamera-ikonet i rækken)
        document.querySelectorAll('.btn-media').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.closest('tr').dataset.id;
                
                // Vi genbruger logikken fra Dashboardet til at åbne media-trinnet
                const modalEl = document.getElementById('appModal');
                if (!modalEl) return;

                fetch(`${App.baseUrl}?module=Collection&controller=Toy&action=media_step&id=${id}`)
                    .then(res => res.text())
                    .then(html => {
                        const modalContent = modalEl.querySelector('.modal-content');
                        modalContent.innerHTML = html;
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();

                        if (typeof App.initMediaUploads === 'function') {
                            App.initMediaUploads();
                        }
                    })
                    .catch(err => console.error('Fejl ved hentning af media modal:', err));
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