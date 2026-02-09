const StorageMgr = {
    baseUrl: '',
    editModal: null,
    deleteModal: null,
    currentDeleteId: null,

    init: function() {
        this.baseUrl = App.baseUrl;
        this.loadPage(1);
        
        // Setup Modals
        const editEl = document.getElementById('editStorageModal');
        if(editEl) this.editModal = new bootstrap.Modal(editEl);

        const delEl = document.getElementById('deleteStorageModal');
        if(delEl) this.deleteModal = new bootstrap.Modal(delEl);

        // Search Listener
        let timeout;
        const searchInput = document.getElementById('searchStorage');
        if(searchInput) {
            searchInput.addEventListener('keyup', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.loadPage(1, e.target.value), 400);
            });
        }

        // Add Form Listener
        const addForm = document.getElementById('storageForm');
        if(addForm) {
            addForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.store();
            });
        }

        this.initDeleteModalListeners();
    },

    initDeleteModalListeners: function() {
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const btnGoStep2 = document.getElementById('btnGoToStep2');
        const btnBack = document.getElementById('btnBackToStep1');
        const btnSimple = document.getElementById('btnDeleteSimple');
        const btnMigrate = document.getElementById('btnMigrateAndDelete');
        const targetSelect = document.getElementById('migrateTargetSelect');

        if(!step1) return;

        btnGoStep2.addEventListener('click', () => {
            this.loadMigrationTargets(this.currentDeleteId);
            step1.classList.add('d-none');
            step2.classList.remove('d-none');
        });

        btnBack.addEventListener('click', () => {
            step2.classList.add('d-none');
            step1.classList.remove('d-none');
        });

        btnSimple.addEventListener('click', () => {
            // Null = ingen migrering (tøm kassen)
            this.executeDelete(this.currentDeleteId, null);
        });

        btnMigrate.addEventListener('click', () => {
            const targetId = targetSelect.value;
            if(targetId) {
                this.executeDelete(this.currentDeleteId, targetId);
            } else {
                alert('Please select a storage unit');
            }
        });
    },

    loadPage: function(page = 1, search = '') {
        const container = document.getElementById('storageGrid');
        if(!container) return;
        
        container.style.opacity = '0.5';
        
        if (!search) {
            const searchInput = document.getElementById('searchStorage');
            if(searchInput) search = searchInput.value;
        }

        const url = `${this.baseUrl}?module=Collection&controller=Storage&action=index&ajax=1&page=${page}&search=${encodeURIComponent(search)}`;

        fetch(url)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                this.attachListeners();
            });
    },

    attachListeners: function() {
        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const tr = btn.closest('tr');
                const data = JSON.parse(tr.dataset.json);
                this.openEditModal(data);
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const tr = btn.closest('tr');
                const data = JSON.parse(tr.dataset.json);
                this.openDeleteModal(data.id, data.box_code + ' - ' + data.name, data.toy_count);
            });
        });
    },

    store: function() {
        const form = document.getElementById('storageForm');
        const formData = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        
        this.toggleLoading(btn, true);

        this.sendRequest('store', formData, () => {
            App.showToast('Storage unit created');
            form.reset();
            this.loadPage();
        }, () => this.toggleLoading(btn, false));
    },

    openEditModal: function(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_box_code').value = data.box_code;
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_location_room').value = data.location_room || '';
        document.getElementById('edit_description').value = data.description || '';
        this.editModal.show();
    },

    update: function() {
        const form = document.getElementById('editStorageForm');
        const formData = new FormData(form);
        const btn = document.querySelector('#editStorageModal .modal-footer button.btn-dark');

        this.toggleLoading(btn, true);

        this.sendRequest('update', formData, () => {
            App.showToast('Storage unit updated');
            this.editModal.hide();
            this.loadPage();
        }, () => this.toggleLoading(btn, false));
    },

    // --- DELETE LOGIC ---

    openDeleteModal: function(id, name, usageCount) {
        this.currentDeleteId = id;
        document.getElementById('del_name').textContent = name;
        document.getElementById('del_usage_count').textContent = usageCount;

        // Reset UI
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2').classList.add('d-none');
        
        // Hvis kassen er tom, skjuler vi "Migrate" muligheden for simpelhed
        const btnGoStep2 = document.getElementById('btnGoToStep2');
        if (usageCount == 0) {
            btnGoStep2.classList.add('d-none');
            document.getElementById('btnDeleteSimple').innerHTML = '<i class="fas fa-trash-alt me-2"></i> Delete Empty Box';
        } else {
            btnGoStep2.classList.remove('d-none');
            document.getElementById('btnDeleteSimple').innerHTML = '<i class="fas fa-trash-alt me-2"></i> Empty Box & Delete';
        }

        this.deleteModal.show();
    },

    loadMigrationTargets: function(currentId) {
        const select = document.getElementById('migrateTargetSelect');
        select.innerHTML = '<option>Loading...</option>';

        fetch(`${this.baseUrl}?module=Collection&controller=Storage&action=get_all_simple`)
            .then(res => res.json())
            .then(data => {
                let html = '<option value="">Select storage unit...</option>';
                data.forEach(item => {
                    // Vis ikke den kasse vi er ved at slette
                    if (item.id != currentId) {
                        html += `<option value="${item.id}">[${item.box_code}] ${item.name}</option>`;
                    }
                });
                select.innerHTML = html;
            });
    },

    executeDelete: function(id, migrateToId) {
        const formData = new FormData();
        formData.append('id', id);
        if (migrateToId) formData.append('migrate_to_id', migrateToId);

        fetch(`${this.baseUrl}?module=Collection&controller=Storage&action=delete`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                this.deleteModal.hide();
                App.showToast('Storage unit deleted successfully!');
                this.loadPage(1);
            } else {
                alert(data.error);
            }
        });
    },

    // Hjælpefunktioner
    sendRequest: function(action, formData, onSuccess, onComplete) {
        fetch(`${this.baseUrl}?module=Collection&controller=Storage&action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                if(onSuccess) onSuccess();
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Request failed');
        })
        .finally(() => {
            if(onComplete) onComplete();
        });
    },

    toggleLoading: function(btn, isLoading) {
        if(isLoading) {
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;
        } else {
            btn.innerHTML = btn.dataset.originalText || 'Save';
            btn.disabled = false;
        }
    }
};

document.addEventListener('DOMContentLoaded', () => StorageMgr.init());