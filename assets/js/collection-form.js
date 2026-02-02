/**
 * COLLECTION FORM
 * Håndterer dropdowns, widget søgning og tilføjelse af parts
 */
App.initDependentDropdowns = function () {
    console.log('Initializing Toy Form logic...');

    const universeSelect = document.getElementById('selectUniverse');
    const manufacturerSelect = document.getElementById('selectManufacturer');
    const lineSelect = document.getElementById('selectLine');
    
    // WIDGET ELEMENTS
    const widgetInput = document.getElementById('inputMasterToyId');
    const widgetWrapper = document.getElementById('masterToyWidgetWrapper'); 
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

    // Hent data fra containeren
    if (container && container.dataset.masterToyItems) {
        try {
            availableMasterToyItems = JSON.parse(container.dataset.masterToyItems);
        } catch (e) { console.error('JSON parse error', e); }
    }

    // --- WIDGET HELPERS ---

    const updateWidgetDisplay = (toy) => {
        const iconEl = document.getElementById('displayToyImgIcon');
        const imgEl = document.getElementById('displayToyImg'); 

        document.getElementById('displayToyTitle').textContent = toy ? toy.name : 'Select Toy...';
        
        if (toy) {
            // Tekst
            const line2 = [toy.release_year, toy.type_name].filter(Boolean).join(' - ');
            document.getElementById('displayToyMeta1').textContent = line2;
            
            const sourceText = toy.source_material_name || (toy.wave_number ? `Wave: ${toy.wave_number}` : '');
            document.getElementById('displayToyMeta2').textContent = sourceText;

            // Billede
            if (toy.image_path) {
                imgEl.src = toy.image_path;
                imgEl.classList.remove('d-none');
                if(iconEl) iconEl.classList.add('d-none');
            } else {
                imgEl.classList.add('d-none');
                imgEl.src = '';
                if(iconEl) {
                    iconEl.classList.remove('d-none');
                    iconEl.className = 'fas fa-robot text-dark fa-2x';
                }
            }
        } else {
            // Reset
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            imgEl.classList.add('d-none');
            imgEl.src = '';
            if(iconEl) {
                iconEl.classList.remove('d-none');
                iconEl.className = 'fas fa-box-open text-muted fa-2x';
            }
        }
    };

    const renderWidgetResults = (filterText = '') => {
        if(!widgetList) return;
        widgetList.innerHTML = '';
        const term = filterText.toLowerCase();
        
        const filtered = currentMasterToysList.filter(t => t.name.toLowerCase().includes(term));

        if (filtered.length === 0) {
            widgetList.innerHTML = '<div class="p-3 text-center text-muted small">No toys found matching "' + filterText + '"</div>';
            return;
        }

        filtered.forEach(toy => {
            const div = document.createElement('div');
            div.className = 'toy-result-item';
            
            // Meta data liste
            let metaParts = [];
            if(toy.release_year) metaParts.push(toy.release_year);
            if(toy.type_name) metaParts.push(toy.type_name);
            if(toy.source_material_name) metaParts.push(toy.source_material_name);

            // Billede vs Ikon logik (NY)
            let imgHtml = '<i class="fas fa-robot text-muted"></i>';
            if (toy.image_path) {
                imgHtml = `<img src="${toy.image_path}" class="toy-thumb-img" alt="${toy.name}">`;
            }

            div.innerHTML = `
                <div class="toy-thumb-container">${imgHtml}</div>
                <div class="flex-grow-1">
                    <div class="toy-title">${toy.name}</div>
                    <div class="text-muted small">${metaParts.join(' &bull; ')}</div>
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
        if (el) { el.innerHTML = `<option value="">${msg}</option>`; el.disabled = true; }
    };
    
    const resetWidget = (msg) => {
        if(widgetCard) {
            widgetCard.classList.add('disabled');
            document.getElementById('displayToyTitle').textContent = msg;
            document.getElementById('displayToyMeta1').textContent = '';
            document.getElementById('displayToyMeta2').textContent = '';
            widgetInput.value = '';
            // Reset ikon
            const iconEl = document.getElementById('displayToyImgIcon');
            const imgEl = document.getElementById('displayToyImg');
            if(imgEl) { imgEl.classList.add('d-none'); imgEl.src = ''; }
            if(iconEl) { iconEl.classList.remove('d-none'); iconEl.className = 'fas fa-box-open text-muted fa-2x'; }
        }
    };

    const populateSelect = (el, data, defaultMsg) => {
        let options = `<option value="">${defaultMsg}</option>`;
        data.forEach((item) => { options += `<option value="${item.id}">${item.name}</option>`; });
        el.innerHTML = options;
        el.disabled = false;
    };

    // --- CORE LOGIC (Child Items) ---

    const refreshExistingRows = () => {
        const selects = container.querySelectorAll('.master-toy-item-select');
        selects.forEach((select) => {
            const currentVal = select.value;
            let options = availableMasterToyItems.length > 0 ? '<option value="">Select Item...</option>' : '<option value="">Unknown Items (Select Toy above first)</option>';
            if (availableMasterToyItems.length > 0) {
                availableMasterToyItems.forEach((mti) => { options += `<option value="${mti.id}">${mti.name} (${mti.type})</option>`; });
            }
            select.innerHTML = options;
            if (currentVal && availableMasterToyItems.some((p) => p.id == currentVal)) select.value = currentVal;
        });
    };

    const addItemRow = async (data = null) => {
        if (availableMasterToyItems.length === 0 && widgetInput.value) {
            try {
                const res = await fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${widgetInput.value}`);
                availableMasterToyItems = await res.json();
            } catch (e) { console.error(e); }
        }

        const index = rowCount++;
        const clone = template.content.cloneNode(true);
        clone.querySelectorAll('[name*="INDEX"]').forEach((el) => { el.name = el.name.replace('INDEX', index); if (el.id) el.id = el.id.replace('INDEX', index); });
        clone.querySelectorAll('[for*="INDEX"]').forEach((el) => { el.setAttribute('for', el.getAttribute('for').replace('INDEX', index)); });

        const masterToyItemSelect = clone.querySelector('.master-toy-item-select');
        const titleSpan = clone.querySelector('.item-display-name');
        const typeSpan = clone.querySelector('.item-type-display'); 

        if (availableMasterToyItems.length > 0) {
            let options = '<option value="">Select Item...</option>';
            availableMasterToyItems.forEach((mti) => { options += `<option value="${mti.id}">${mti.name} (${mti.type})</option>`; });
            masterToyItemSelect.innerHTML = options;
        } else {
            masterToyItemSelect.innerHTML = '<option value="">Unknown Items (Select Toy above first)</option>';
        }

        masterToyItemSelect.addEventListener('change', function () {
            const mti = availableMasterToyItems.find((p) => p.id == this.value);
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
            clone.querySelector('.child-item-row').prepend(idInput);

            titleSpan.textContent = data.master_toy_item_name || 'Item';
            if (data.master_toy_item_type && typeSpan) typeSpan.textContent = ` (${data.master_toy_item_type})`;

            if (masterToyItemSelect) masterToyItemSelect.value = data.master_toy_item_id;
            clone.querySelector('.input-loose').checked = (data.is_loose == 1);
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

            clone.querySelector('.remove-row-btn').onclick = function (e) {
                e.preventDefault();
                if (data.id) App.deleteToyItem(data.id, this);
            };
        } else {
            clone.querySelector('.remove-row-btn').onclick = function (e) {
                e.preventDefault();
                e.target.closest('.child-item-row').remove();
                if(countBadge) countBadge.textContent = `${container.querySelectorAll('.child-item-row').length} items`;
            };
        }

        container.appendChild(clone);
        if(countBadge) countBadge.textContent = `${container.querySelectorAll('.child-item-row').length} items`;
        if (!data) container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    // Init existing items
    if (container && container.dataset.items) {
        try {
            const existingItems = JSON.parse(container.dataset.items);
            if (Array.isArray(existingItems)) existingItems.forEach((item) => addItemRow(item));
        } catch (e) { console.error('Error parsing items', e); }
    }

    // --- EVENTS & DROPDOWNS ---
    if(widgetCard) {
        widgetCard.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;
            if (widgetOverlay.classList.contains('show')) {
                widgetOverlay.classList.remove('show');
            } else {
                widgetOverlay.classList.add('show');
                widgetSearch.value = '';
                widgetSearch.focus();
                renderWidgetResults();
            }
        });
    }

    if(widgetSearch) widgetSearch.addEventListener('keyup', (e) => renderWidgetResults(e.target.value));

    document.addEventListener('click', function(event) {
        if (widgetOverlay && widgetOverlay.classList.contains('show')) {
            if (!widgetCard.contains(event.target) && !widgetOverlay.contains(event.target)) {
                widgetOverlay.classList.remove('show');
            }
        }
    });

    const loadManufacturers = (universeId) => {
        if (!manufacturerSelect) return;
        manufacturerSelect.innerHTML = '<option>Loading...</option>'; manufacturerSelect.disabled = true;
        resetSelect(lineSelect, 'Select Manufacturer first...');
        resetWidget('Select Line first...');
        if (!universeId) { resetSelect(manufacturerSelect, 'Select Universe first...'); return; }

        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_manufacturers&universe_id=${universeId}`)
            .then((res) => res.json()).then((data) => {
                populateSelect(manufacturerSelect, data, 'Select Manufacturer...');
                if(data.length === 1) { manufacturerSelect.value = data[0].id; manufacturerSelect.dispatchEvent(new Event('change')); }
            });
    };

    const loadLines = (manId) => {
        lineSelect.innerHTML = '<option>Loading...</option>'; lineSelect.disabled = true;
        resetWidget('Select Line first...');
        if (!manId) return;
        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_lines&manufacturer_id=${manId}&universe_id=${universeSelect.value}`)
            .then((res) => res.json()).then((data) => {
                populateSelect(lineSelect, data, 'Select Line...');
                if(data.length === 1) { lineSelect.value = data[0].id; lineSelect.dispatchEvent(new Event('change')); }
            });
    };

    const loadToys = (lineId) => {
        widgetInput.value = ''; currentMasterToysList = [];
        if (widgetCard) widgetCard.classList.add('disabled');
        document.getElementById('displayToyTitle').textContent = 'Loading...';
        if (!lineId) { document.getElementById('displayToyTitle').textContent = 'Select Line first...'; return; }

        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toys&line_id=${lineId}`)
            .then((res) => res.json()).then((data) => {
                currentMasterToysList = data;
                if (widgetCard) widgetCard.classList.remove('disabled');
                document.getElementById('displayToyTitle').textContent = 'Select Toy / Set...';
            });
    };

    const loadMasterToyItems = (toyId) => {
        availableMasterToyItems = [];
        if (!toyId) { refreshExistingRows(); return; }
        fetch(`${App.baseUrl}?module=Collection&controller=Api&action=get_master_toy_items&master_toy_id=${toyId}`)
            .then((res) => res.json()).then((data) => {
                availableMasterToyItems = data;
                refreshExistingRows();
            });
    };

    if (universeSelect) {
        universeSelect.addEventListener('change', (e) => loadManufacturers(e.target.value));
        if (universeSelect.value && manufacturerSelect.options.length <= 1) loadManufacturers(universeSelect.value);
    }
    if (manufacturerSelect) manufacturerSelect.addEventListener('change', (e) => loadLines(e.target.value));
    if (lineSelect) lineSelect.addEventListener('change', (e) => loadToys(e.target.value));
    if (btnAddItem) btnAddItem.addEventListener('click', () => addItemRow());

    // --- FIX: INITIALISER WIDGET I EDIT MODE ---
    if (widgetWrapper) {
        if (widgetWrapper.dataset.allToys) {
            try { currentMasterToysList = JSON.parse(widgetWrapper.dataset.allToys); } catch (e) { console.error('Error parsing all toys', e); }
        }
        if (widgetWrapper.dataset.selectedToy) {
            try {
                const selectedToy = JSON.parse(widgetWrapper.dataset.selectedToy);
                if (selectedToy) {
                    updateWidgetDisplay(selectedToy);
                    if(widgetCard) widgetCard.classList.remove('disabled');
                }
            } catch (e) { console.error('Error parsing selected toy', e); }
        }
    }

    // Ajax Submit
    const form = document.getElementById('addToyForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (container.querySelectorAll('.child-item-row').length === 0) {
                alert('You must add at least one Item before saving.');
                btnAddItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...'; submitBtn.disabled = true;

            fetch(form.action, { method: 'POST', body: formData })
                .then((response) => {
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json().then(data => {
                            if (data.success) window.location.reload();
                            else { alert('Error: ' + data.error); submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
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
                .catch((err) => { alert('Error saving.'); submitBtn.innerHTML = originalBtnText; submitBtn.disabled = false; });
        });
    }
};