const MasterToyMgr = {
    // Cache til subjects data
    allSubjects: [],

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

        // Search Delay
        let timeout;
        if(this.search) {
            this.search.addEventListener('keyup', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.loadPage(1), 400);
            });
        }

        this.attachGridListeners();

        // --- MUTATION OBSERVER (VIGTIG: Denne får modalen til at virke) ---
        const modalEl = document.getElementById('appModal');
        if(modalEl) {
            const observer = new MutationObserver(() => {
                const form = document.getElementById('masterToyForm');
                // Hvis formen findes og ikke er startet endnu
                if(form && !form.dataset.initialized) {
                    form.dataset.initialized = "true";
                    this.initForm(); 
                }
            });
            observer.observe(modalEl, { childList: true, subtree: true });
        }
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
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                if(confirm('Delete this Master Toy? This cannot be undone.')) {
                    MasterToyMgr.executeDelete(id);
                }
            });
        });

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                MasterToyMgr.openEdit(id);
            });
        });

        document.querySelectorAll('.btn-media').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.closest('tr').dataset.id;
                alert('Media manager kommer i Step 3 for ID: ' + id);
            });
        });
    },

    openUniverseSelect: function() {
        App.openModal('Catalog', 'MasterToy', 'modal_step1');
    },

    goToStep2: function(universeId) {
        App.openModal('Catalog', 'MasterToy', 'modal_step2', { universe_id: universeId });
    },

    openEdit: function(id) {
        App.openModal('Catalog', 'MasterToy', 'modal_step2', { id: id });
    },

    // --- FORM LOGIC ---
    
    initForm: function() {
        console.log('Initializing Master Toy Form...');
        
        const container = document.getElementById('itemsContainer');
        const template = document.getElementById('itemRowTemplate');

        if (!container || !template) return;

        // 1. Hent data fra dataset
        let items = [];
        try {
            items = JSON.parse(container.dataset.items || '[]');
            this.allSubjects = JSON.parse(container.dataset.subjects || '[]');
        } catch (e) {
            console.error('JSON parse error', e);
        }

        // 2. Render eksisterende items
        container.innerHTML = '';
        items.forEach(item => this.renderRow(item));
        this.updateUI();
    },

    // Tilføj ny række (Kaldes via onclick i HTML)
    addItem: function() {
        this.renderRow({ quantity: 1 });
        this.updateUI();
        
        // Scroll til bunden
        const container = document.getElementById('itemsContainer');
        if(container) setTimeout(() => container.scrollTop = container.scrollHeight, 50);
    },

    // Fjern række (Kaldes via onclick i HTML)
    removeItem: function(btn) {
        const row = btn.closest('.item-row');
        if(row) {
            row.remove();
            this.updateUI();
        }
    },

    // Helper til at tegne en række
    renderRow: function(item) {
        const container = document.getElementById('itemsContainer');
        const template = document.getElementById('itemRowTemplate');
        
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.item-row');
        
        // Unikt ID til inputs
        const uid = Date.now() + Math.floor(Math.random() * 1000);

        // Opdater navne i inputs
        row.querySelectorAll('[name*="UID"]').forEach(el => {
            el.name = el.name.replace('UID', uid);
        });

        // Sæt simple værdier
        if (item.variation_name) row.querySelector('.input-variant').value = item.variation_name;
        if (item.quantity) row.querySelector('.input-qty').value = item.quantity;

        // Sæt Subject Widget Værdi
        const subjectInput = row.querySelector('.input-subject-id');
        const displayCard = row.querySelector('.subject-display-card');
        
        if (item.subject_id) {
            subjectInput.value = item.subject_id;
            // Find subject data for at vise navn m.m.
            const subject = this.allSubjects.find(s => s.id == item.subject_id);
            if(subject) {
                this.updateSubjectDisplay(displayCard, subject);
            }
        }

        container.appendChild(row);
    },

    updateUI: function() {
        const container = document.getElementById('itemsContainer');
        const badge = document.getElementById('itemCountBadge');
        const emptyMsg = document.getElementById('emptyItemsMsg');

        if(container && badge) {
            const count = container.querySelectorAll('.item-row').length;
            badge.textContent = count + " item(s)";
            
            if(emptyMsg) {
                emptyMsg.style.display = count === 0 ? 'block' : 'none';
            }
        }
    },

    // --- SUBJECT WIDGET LOGIC ---

    toggleSearch: function(cardEl) {
        const wrapper = cardEl.closest('.subject-selector-wrapper');
        const dropdown = wrapper.querySelector('.subject-search-dropdown');
        const input = dropdown.querySelector('.search-input');
        
        // Luk alle andre åbne dropdowns
        document.querySelectorAll('.subject-search-dropdown').forEach(el => {
            if(el !== dropdown) el.classList.add('d-none');
        });

        const isHidden = dropdown.classList.contains('d-none');
        if (isHidden) {
            dropdown.classList.remove('d-none');
            input.value = ''; 
            input.focus();
            this.filterSubjects(input); // Vis initial liste
            
            // Klik udenfor lukker dropdown
            setTimeout(() => {
                const closeHandler = (e) => {
                    if (!wrapper.contains(e.target)) {
                        dropdown.classList.add('d-none');
                        document.removeEventListener('click', closeHandler);
                    }
                };
                document.addEventListener('click', closeHandler);
            }, 0);
        } else {
            dropdown.classList.add('d-none');
        }
    },

    filterSubjects: function(inputEl) {
        const term = inputEl.value.toLowerCase();
        const listEl = inputEl.closest('.subject-search-dropdown').querySelector('.results-list');
        
        // Filtrer og vis max 50 resultater
        const matches = this.allSubjects.filter(s => {
            return s.name.toLowerCase().includes(term) || 
                   (s.type && s.type.toLowerCase().includes(term));
        }).slice(0, 50);

        if (matches.length === 0) {
            listEl.innerHTML = '<div class="p-2 text-muted small text-center">No matches found</div>';
            return;
        }

        let html = '';
        matches.forEach(s => {
            // Byg info-tekst: Type ? Faction
            // Bruger en simpel visning, da 'faction' måske ikke findes endnu
            const metaParts = [];
            if(s.type) metaParts.push(s.type);
            if(s.faction) metaParts.push(s.faction);
            const meta = metaParts.join(' &bull; ');
            
            html += `
                <div class="subject-result-item" onclick="MasterToyMgr.selectSubject(this, ${s.id})">
                    <div class="fw-bold text-dark">${s.name}</div>
                    <div class="text-muted small">${meta}</div>
                </div>
            `;
        });
        listEl.innerHTML = html;
    },

    selectSubject: function(itemEl, id) {
        const wrapper = itemEl.closest('.subject-selector-wrapper');
        const input = wrapper.parentNode.querySelector('.input-subject-id');
        const displayCard = wrapper.querySelector('.subject-display-card');
        const dropdown = wrapper.querySelector('.subject-search-dropdown');

        // Sæt hidden value
        input.value = id;

        // Opdater display kortet
        const subject = this.allSubjects.find(s => s.id == id);
        if(subject) {
            this.updateSubjectDisplay(displayCard, subject);
        }

        // Luk dropdown
        dropdown.classList.add('d-none');
    },

    updateSubjectDisplay: function(cardEl, subject) {
        const nameEl = cardEl.querySelector('.subject-name');
        const metaEl = cardEl.querySelector('.subject-meta');
        const iconEl = cardEl.querySelector('.subject-icon');

        nameEl.textContent = subject.name;
        nameEl.classList.remove('text-muted'); 
        
        const metaParts = [];
        if(subject.type) metaParts.push(subject.type);
        if(subject.faction) metaParts.push(subject.faction);
        metaEl.innerHTML = metaParts.join(' &bull; ');
        metaEl.style.display = 'block';

        // Opdater ikon baseret på type
        if(subject.type === 'Character') iconEl.className = 'fas fa-user subject-icon';
        else if(subject.type === 'Vehicle') iconEl.className = 'fas fa-fighter-jet subject-icon';
        else if(subject.type === 'Creature') iconEl.className = 'fas fa-dragon subject-icon';
        else iconEl.className = 'fas fa-cube subject-icon';
    },

    // --- SUBMIT & DELETE ---

    submitForm: function() {
        const form = document.getElementById('masterToyForm');
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const id = formData.get('id');
        const action = id ? 'update' : 'store';

        const btn = form.querySelector('button[onclick*="submitForm"]');
        let originalText = '';
        if(btn) {
            originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            btn.disabled = true;
        }

        fetch(`${this.baseUrl}?module=Catalog&controller=MasterToy&action=${action}`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const modalEl = document.getElementById('appModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) modal.hide();

                App.showToast(id ? 'Toy updated successfully!' : 'Toy created successfully!');
                this.loadPage(1);

                if(!id) {
                    setTimeout(() => {
                        if(confirm('Toy created! Do you want to upload photos now?')) {
                            alert('Media Modal TODO');
                        }
                    }, 500);
                }
            } else {
                alert(data.error);
                if(btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred.');
            if(btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
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