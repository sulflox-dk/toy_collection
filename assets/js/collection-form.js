/**
 * FORM & WIDGET LOGIC
 */

App.initDependentDropdowns = function () {
    console.log('Initializing Toy Form logic...');

    const universeSelect = document.getElementById('selectUniverse');
    const manufacturerSelect = document.getElementById('selectManufacturer');
    const lineSelect = document.getElementById('selectLine');
    
    // WIDGET ELEMENTS
    const widgetInput = document.getElementById('inputMasterToyId');
    const widgetCard = document.getElementById('masterToyDisplayCard');
    const widgetOverlay = document.getElementById('masterToyOverlay');
    const widgetSearch = document.getElementById('inputToySearch');
    const widgetList = document.getElementById('toyResultsList');

    const btnAddItem = document.getElementById('btnAddItemRow');
    const container = document.getElementById('childItemsContainer');
    const template = document.getElementById('childRowTemplate');
    const countBadge = document.getElementById('itemCountBadge');

    let availableMasterToyItems = [];
    let currentMasterToysList = [];   
    let rowCount = 0;

    // Hent data fra containeren (Edit mode data og items)
    if (container) {
        try {
            if (container.dataset.masterToyItems) {
                availableMasterToyItems = JSON.parse(container.dataset.masterToyItems);
            }
        } catch (e) {
            console.error('JSON parse error in master items', e);
        }
    }

    // --- WIDGET LOGIK ---

    // Opdaterer display kortet med det valgte toy (Når kortet er LUKKET)
    const updateWidgetDisplay = (toy) => {
        const iconEl = document.getElementById('displayToyImgIcon');
        // const imgEl = document.getElementById('displayToyImg'); // Forberedelse til billeder

        document.getElementById('displayToyTitle').textContent = toy ? toy.name : 'Select Toy...';
        
        if (toy) {
            // Linie 2: Year + Type
            const line2 = [toy.release_year, toy.type_name].filter(Boolean).join(' - ');
            document.getElementById('displayToyMeta1').textContent = line2;
            
            // Linie 3: Source Material (Filmen/Serien) - Fallback til Wave nummer
            const sourceText = toy.source_material_name || (toy.wave_number ? `Wave: ${toy.wave_number}` : '');
            document.getElementById('displayToyMeta2').textContent = sourceText;

            if(iconEl) iconEl.className = 'fas fa-robot text-dark fa-2x';
        } else {
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            if(iconEl) iconEl.className = 'fas fa-box-open text-muted fa-2x';
        }
    };

    // Genererer HTML listen i overlayet (Når kortet er ÅBENT)
    const renderWidgetResults = (filterText = '') => {
        if(!widgetList) return;
        widgetList.innerHTML = '';
        const term = filterText.toLowerCase();
        
        // Filtrer listen
        const filtered = currentMasterToysList.filter(t => t.name.toLowerCase().includes(term));

        if (filtered.length === 0) {
            widgetList.innerHTML = '<div class="p-3 text-center text-muted small">No toys found matching "' + filterText + '"</div>';
            return;
        }

        filtered.forEach(toy => {
            const div = document.createElement('div');
            div.className = 'toy-result-item';
            
            // Vi bygger en liste af meta-data (År, Type, Kilde)
            let metaParts = [];
            if(toy.release_year) metaParts.push(toy.release_year);
            if(toy.type_name) metaParts.push(toy.type_name);
            // Her tager vi kilden med (The Empire Strikes Back), hvis den findes
            if(toy.source_material_name) metaParts.push(toy.source_material_name);

            div.innerHTML = `
                <div class="toy-thumb-container">
                    <i class="fas fa-robot text-muted"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="toy-title">${toy.name}</div>
                    <div class="text-muted small">
                        ${metaParts.join(' &bull; ')}
                    </div>
                </div>
            `;
            
            div.addEventListener('click', () => {
                widgetInput.value = toy.id;
                updateWidgetDisplay(toy);
                widgetOverlay.classList.remove('show');
                loadMasterToyItems(toy.id);
            });
            
            widgetList.appendChild(div);
        });
    };

    const resetSelect = (el, msg) => {
        if (el) {
            el.innerHTML = `<option value="">${msg}</option>`;
            el.disabled = true;
        }
    };
    
    const resetWidget = (msg) => {
        if(widgetCard) {
            widgetCard.classList.add('disabled');
            document.getElementById('displayToyTitle').textContent = msg;
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            widgetInput.value = '';
        }
    };

    const populateSelect = (el, data, defaultMsg) => {
        let options = `<option value="">${defaultMsg}</option>`;
        data.forEach((item) => {
            options += `<option value="${item.id}">${item.name}</option>`;
        });
        el.innerHTML = options;
        el.disabled = false;
    };

    const updateCount = () => {
        const count = container.querySelectorAll('.child-item-row').length;
        if (countBadge) countBadge.textContent = `${count} items`;
    };

    // --- CORE ITEM LOGIC (Child Items) ---
    
    const refreshExistingRows = () => {
        const selects = container.querySelectorAll('.master-toy-item-select');
        selects.forEach((select) => {
            const currentVal = select.value;
            let options =
                availableMasterToyItems.length > 0
                    ? '<option value="">Select Item...</option>'
                    : '<option value="">Unknown Items (Select Toy above first)</option>';

            if (availableMasterToyItems.length > 0) {
                availableMasterToyItems.forEach((mti) => {
                    options += `<option value="${mti.id}">${mti.name} (${mti.type})</option>`;
                });
            }
            select.innerHTML = options;
            if (currentVal && availableMasterToyItems.some((p) => p.id == currentVal)) {
                select.value = currentVal;
            }
        });
    };

    const addItemRow = async (data = null) => {
        if (availableMasterToyItems.length === 0 && widgetInput.value) {
            try {
                const res = await fetch(
                    `${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${widgetInput.value}`,
                );
                availableMasterToyItems = await res.json();
            } catch (e) {
                console.error(e);
            }
        }

        const index = rowCount++;
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.child-item-row');

        clone.querySelectorAll('[name*="INDEX"]').forEach((el) => {
            el.name = el.name.replace('INDEX', index);
            if (el.id) el.id = el.id.replace('INDEX', index);
        });
        clone.querySelectorAll('[for*="INDEX"]').forEach((el) => {
            el.setAttribute('for', el.getAttribute('for').replace('INDEX', index));
        });

        const masterToyItemSelect = clone.querySelector('.master-toy-item-select');
        const titleSpan = clone.querySelector('.item-display-name');
        const typeSpan = clone.querySelector('.item-type-display'); 

        if (availableMasterToyItems.length > 0) {
            let options = '<option value="">Select Item...</option>';
            availableMasterToyItems.forEach((mti) => {
                options += `<option value="${mti.id}">${mti.name} (${mti.type})</option>`;
            });
            masterToyItemSelect.innerHTML = options;
        } else {
            masterToyItemSelect.innerHTML = '<option value="">Unknown Items (Select Toy above first)</option>';
        }

        masterToyItemSelect.addEventListener('change', function () {
            const selectedId = this.value;
            const mti = availableMasterToyItems.find((p) => p.id == selectedId);
            if (mti) {
                titleSpan.textContent = mti.name;
                if (typeSpan) typeSpan.textContent = ` (${mti.type})`;
            } else {
                titleSpan.textContent = 'New Item';
                if (typeSpan) typeSpan.textContent = '';
            }
        });

        if (data) {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `items[${index}][id]`;
            idInput.value = data.id;
            idInput.className = 'item-db-id'; 
            row.prepend(idInput);

            titleSpan.textContent = data.master_toy_item_name || 'Item';
            if (data.master_toy_item_type && typeSpan) {
                typeSpan.textContent = ` (${data.master_toy_item_type})`;
            }

            if (masterToyItemSelect) masterToyItemSelect.value = data.master_toy_item_id;
            if (data.is_loose == 1) clone.querySelector('.input-loose').checked = true;
            else clone.querySelector('.input-loose').checked = false;

            clone.querySelector('.input-condition').value = data.condition || '';
            clone.querySelector('.input-repro').value = data.is_reproduction || '';
            clone.querySelector('[name*="[purchase_date]"]').value = data.purchase_date || '';
            clone.querySelector('[name*="[purchase_price]"]').value = data.purchase_price || '';
            clone.querySelector('[name*="[source_id]"]').value = data.source_id || '';
            clone.querySelector('[name*="[acquisition_status]"]').value = data.acquisition_status || '';
            clone.querySelector('[name*="[expected_arrival_date]"]').value = data.expected_arrival_date || '';
            clone.querySelector('[name*="[personal_item_id]"]').value = data.personal_item_id || '';
            clone.querySelector('[name*="[storage_id]"]').value = data.storage_id || '';
            clone.querySelector('[name*="[user_comments]"]').value = data.user_comments || '';

            const deleteBtn = clone.querySelector('.remove-row-btn');
            deleteBtn.onclick = function (e) {
                e.preventDefault();
                if (data.id) {
                    App.deleteToyItem(data.id, this);
                }
            };
        } else {
            const deleteBtn = clone.querySelector('.remove-row-btn');
            deleteBtn.onclick = function (e) {
                e.preventDefault();
                e.target.closest('.child-item-row').remove();
                updateCount();
            };
        }

        container.appendChild(clone);
        updateCount();

        if (!data) {
            const newRow = container.lastElementChild;
            if (newRow) newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    if (container && container.dataset.items) {
        try {
            const existingItems = JSON.parse(container.dataset.items);
            if (Array.isArray(existingItems)) {
                existingItems.forEach((item) => addItemRow(item));
            }
        } catch (e) {
            console.error('Error parsing items', e);
        }
    }

    // --- WIDGET EVENT LISTENERS ---
    if(widgetCard) {
        widgetCard.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;
            
            const isMsgOpen = widgetOverlay.classList.contains('show');
            if (isMsgOpen) {
                widgetOverlay.classList.remove('show');
            } else {
                widgetOverlay.classList.add('show');
                widgetSearch.value = '';
                widgetSearch.focus();
                renderWidgetResults();
            }
        });
    }

    if(widgetSearch) {
        widgetSearch.addEventListener('keyup', (e) => {
            renderWidgetResults(e.target.value);
        });
    }

    document.addEventListener('click', function(event) {
        if (widgetOverlay && widgetOverlay.classList.contains('show')) {
            if (!widgetCard.contains(event.target) && !widgetOverlay.contains(event.target)) {
                widgetOverlay.classList.remove('show');
            }
        }
    });

    // --- DROPDOWNS ---
    const autoSelectIfSingle = (element, data) => {
        if (data.length === 1) {
            element.value = data[0].id;
            element.dispatchEvent(new Event('change'));
        }
    };

    const loadManufacturers = (universeId) => {
        if (!manufacturerSelect) return;
        manufacturerSelect.innerHTML = '<option>Loading...</option>';
        manufacturerSelect.disabled = true;
        resetSelect(lineSelect, 'Select Manufacturer first...');
        resetWidget('Select Line first...');

        if (!universeId) {
            resetSelect(manufacturerSelect, 'Select Universe first...');
            return;
        }

        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_manufacturers&universe_id=${universeId}`)
            .then((res) => res.json())
            .then((data) => {
                populateSelect(manufacturerSelect, data, 'Select Manufacturer...');
                autoSelectIfSingle(manufacturerSelect, data);
            });
    };

    const loadLines = (manId) => {
        lineSelect.innerHTML = '<option>Loading...</option>';
        lineSelect.disabled = true;
        resetWidget('Select Line first...');
        const uniId = universeSelect.value;
        if (!manId) return;

        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_lines&manufacturer_id=${manId}&universe_id=${uniId}`)
            .then((res) => res.json())
            .then((data) => {
                populateSelect(lineSelect, data, 'Select Line...');
                autoSelectIfSingle(lineSelect, data);
            });
    };

    const loadToys = (lineId) => {
        widgetInput.value = '';
        currentMasterToysList = [];
        if (widgetCard) widgetCard.classList.add('disabled');
        document.getElementById('displayToyTitle').textContent = 'Loading...';
        
        if (!lineId) {
            document.getElementById('displayToyTitle').textContent = 'Select Line first...';
            return;
        }

        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toys&line_id=${lineId}`)
            .then((res) => res.json())
            .then((data) => {
                currentMasterToysList = data;
                if (widgetCard) widgetCard.classList.remove('disabled');
                document.getElementById('displayToyTitle').textContent = 'Select Toy / Set...';
            });
    };

    const loadMasterToyItems = (toyId) => {
        availableMasterToyItems = [];
        if (!toyId) {
            refreshExistingRows();
            return;
        }
        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${toyId}`)
            .then((res) => res.json())
            .then((data) => {
                availableMasterToyItems = data;
                refreshExistingRows();
            });
    };

    if (universeSelect) {
        universeSelect.addEventListener('change', (e) => loadManufacturers(e.target.value));
        if (universeSelect.value && manufacturerSelect && manufacturerSelect.options.length <= 1) {
            loadManufacturers(universeSelect.value);
        }
    }
    if (manufacturerSelect) manufacturerSelect.addEventListener('change', (e) => loadLines(e.target.value));
    if (lineSelect) lineSelect.addEventListener('change', (e) => loadToys(e.target.value));
    if (btnAddItem) btnAddItem.addEventListener('click', () => addItemRow());

    // Ajax Submit Logic
    const form = document.getElementById('addToyForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const rowCount = container.querySelectorAll('.child-item-row').length;
            if (rowCount === 0) {
                alert('You must add at least one Item (Figure/Part) before saving.');
                btnAddItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;

            fetch(form.action, { method: 'POST', body: formData })
                .then((response) => {
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json().then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Error saving: ' + (data.error || 'Unknown error'));
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnText;
                            }
                        });
                    } else {
                        return response.text().then(html => {
                            const modalContent = form.closest('.modal-content');
                            if (modalContent) {
                                modalContent.innerHTML = html;
                                if (typeof App.initMediaUploads === 'function') App.initMediaUploads();
                            }
                        });
                    }
                })
                .catch((err) => {
                    alert('An error occurred while saving.');
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                });
        });
    }
};