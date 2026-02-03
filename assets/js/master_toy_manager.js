const MasterToyMgr = {
    init: function() {
        this.baseUrl = App.baseUrl;
        this.container = document.getElementById('masterToyGridContainer');
        this.search = document.getElementById('searchName');
        
        this.filterUni = document.getElementById('filterUniverse');
        this.filterLine = document.getElementById('filterLine');
        this.filterSource = document.getElementById('filterSource');

        // Filters
        const filters = [this.filterUni, this.filterLine, this.filterSource];
        filters.forEach(f => {
            if(f) f.addEventListener('change', () => this.loadPage(1));
        });

        // Search
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
            module: 'Catalog', 
            controller: 'MasterToy', 
            action: 'index',
            ajax_grid: 1, 
            page: page,
            universe_id: this.filterUni.value,
            line_id: this.filterLine.value,
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

        // EDIT (Brug App.openModal)
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                MasterToyMgr.openEdit(id);
            });
        });

        // MEDIA (Placeholder)
        document.querySelectorAll('.btn-media').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                alert('Media manager kommer i Step 3 for ID: ' + id);
            });
        });
    },

    // --- STEP 1: SELECT UNIVERSE ---
    openUniverseSelect: function() {
        // Vi bruger App.openModal til at hente og vise modalen i #appModal
        App.openModal('Catalog', 'MasterToy', 'modal_step1');
    },

    // --- STEP 2: FORM (CREATE) ---
    goToStep2: function(universeId) {
        // App.openModal vil automatisk erstatte indholdet i #appModal
        App.openModal('Catalog', 'MasterToy', 'modal_step2', { universe_id: universeId });
    },

    // --- EDIT ---
    openEdit: function(id) {
        App.openModal('Catalog', 'MasterToy', 'modal_step2', { id: id });
    },

    // --- ITEMS ROW MANAGER (Kaldes fra <script> i modal viewet) ---
    setupItemsManager: function(formEl) {
        if (!formEl) return;
        
        const container = formEl.querySelector('#itemsContainer');
        const btnAdd = formEl.querySelector('#btnAddRow');
        const emptyMsg = formEl.querySelector('#emptyItemsMsg');
        const countBadge = formEl.querySelector('#itemCountBadge');
        const template = document.getElementById('itemRowTemplate');
        
        // RETTET: Hent data fra containerens dataset i stedet for formen
        let items = [];
        if (container && container.dataset.items) {
            try {
                items = JSON.parse(container.dataset.items);
            } catch (e) {
                console.error('JSON parse error in items', e);
            }
        }

        const updateUI = () => {
            if(countBadge) countBadge.textContent = items.length;
            if(items.length === 0) {
                if(emptyMsg) emptyMsg.style.display = 'block';
            } else {
                if(emptyMsg) emptyMsg.style.display = 'none';
            }
        };

        const renderRow = (item, index) => {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.card');
            
            // Opdater index i inputs
            row.querySelectorAll('[name*="INDEX"]').forEach(input => {
                input.name = input.name.replace('INDEX', index);
            });
            
            // Sæt værdier
            const subjSel = row.querySelector('.input-subject');
            if(subjSel) subjSel.value = item.subject_id || '';
            
            const varInput = row.querySelector('.input-variant');
            if(varInput) varInput.value = item.variation_name || '';
            
            const qtyInput = row.querySelector('.input-qty');
            if(qtyInput) qtyInput.value = item.quantity || 1;

            // Delete event
            row.querySelector('.remove-row').addEventListener('click', () => {
                row.remove();
                items.splice(index, 1); 
                updateUI();
            });

            container.appendChild(row);
        };

        const initRender = () => {
            container.innerHTML = '';
            items.forEach((item, idx) => renderRow(item, idx));
            updateUI();
        };

        const addItem = () => {
            const newItem = {subject_id: '', variation_name: '', quantity: 1};
            items.push(newItem);
            renderRow(newItem, items.length - 1);
            updateUI();
        };

        if(btnAdd) {
            const newBtn = btnAdd.cloneNode(true);
            btnAdd.parentNode.replaceChild(newBtn, btnAdd);
            newBtn.addEventListener('click', addItem);
        }
        
        initRender();
    },

    // --- SUBMIT ---
    submitForm: function() {
        const form = document.getElementById('masterToyForm');
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const id = formData.get('id');
        const action = id ? 'update' : 'store';

        // Lås knappen
        const btn = form.querySelector('button[onclick*="submitForm"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        btn.disabled = true;

        fetch(`${this.baseUrl}?module=Catalog&controller=MasterToy&action=${action}`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Luk modal ved at bruge bootstrap instansen på #appModal
                const modalEl = document.getElementById('appModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) modal.hide();

                App.showToast(id ? 'Toy updated successfully!' : 'Toy created successfully!');
                this.loadPage(1);

                if(!id) {
                   setTimeout(() => {
                        if(confirm('Toy created! Do you want to upload photos now?')) {
                            // Her kommer step 3 integrationen
                            alert('Opening Media Modal...');
                        }
                   }, 500);
                }
            } else {
                alert(data.error);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
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