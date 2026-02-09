document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = App.baseUrl;

    // --- CREATE ---
    document.getElementById('createUniverseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(`${baseUrl}?module=Universe&controller=Universe&action=store`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Her manglede vi feedback før!
                document.getElementById('createUniverseForm').reset(); // Reset form
                if(App.showToast) App.showToast('Universe created successfully!'); // Vis toast
                
                // Vi reloader siden her (fordi vi ikke har grid-ajax på universe endnu)
                setTimeout(() => window.location.reload(), 1000); 
            } 
            else alert(data.error);
        });
    });

    // --- EDIT ---
    const editModal = new bootstrap.Modal(document.getElementById('editUniverseModal'));
    
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const data = JSON.parse(row.dataset.json);

            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_slug').value = data.slug;
            document.getElementById('edit_sort').value = data.sort_order;
            document.getElementById('edit_show').checked = (data.show_on_dashboard == 1);

            editModal.show();
        });
    });

    document.getElementById('editUniverseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        // Checkbox fix: hvis den ikke er checked sender den ikke noget, så vi håndterer det i PHP
        // eller sikrer at 'show_on_dashboard' key altid sendes hvis vi vil være eksplicitte. 
        // Controller koden `isset()` håndterer det fint.

        fetch(`${baseUrl}?module=Universe&controller=Universe&action=update`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) window.location.reload();
            else alert(data.error);
        });
    });

    // --- DELETE / MIGRATE ---
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUniverseModal'));
    let currentDeleteId = null;

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const data = JSON.parse(row.dataset.json);
            currentDeleteId = data.id;

            document.getElementById('del_name').textContent = data.name;
            document.getElementById('del_line_count').textContent = data.line_count;
            document.getElementById('del_source_count').textContent = data.source_count;

            // Reset UI
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');

            // Populate Migrate Dropdown
            const select = document.getElementById('migrateTargetSelect');
            select.innerHTML = '';
            document.querySelectorAll('tr[data-id]').forEach(tr => {
                const u = JSON.parse(tr.dataset.json);
                if (u.id != currentDeleteId) {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    select.appendChild(opt);
                }
            });

            deleteModal.show();
        });
    });

    // Step 1 buttons
    document.getElementById('btnDeleteSimple').addEventListener('click', () => executeDelete(currentDeleteId, null));
    
    document.getElementById('btnGoToStep2').addEventListener('click', () => {
        const select = document.getElementById('migrateTargetSelect');
        if (select.options.length === 0) {
            alert('No other universes to migrate to!');
            return;
        }
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.remove('d-none');
    });

    // Step 2 buttons
    document.getElementById('btnBackToStep1').addEventListener('click', () => {
        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step1').classList.remove('d-none');
    });

    document.getElementById('btnMigrateAndDelete').addEventListener('click', () => {
        const targetId = document.getElementById('migrateTargetSelect').value;
        if(targetId) executeDelete(currentDeleteId, targetId);
    });

    function executeDelete(id, migrateToId) {
        const formData = new FormData();
        formData.append('id', id);
        if (migrateToId) formData.append('migrate_to_id', migrateToId);

        fetch(`${baseUrl}?module=Universe&controller=Universe&action=delete`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) window.location.reload();
            else alert(data.error);
        });
    }
});