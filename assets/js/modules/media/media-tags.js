document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = typeof App !== 'undefined' ? App.baseUrl : '';

    // --- 1. CREATE TAG ---
    const createForm = document.getElementById('createTagForm');
    if(createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            const input = this.querySelector('input');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Creating...';

            const formData = new FormData();
            formData.append('tag_name', input.value);

            fetch(`${baseUrl}?module=Media&controller=MediaTags&action=store`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert(data.error);
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });
        });
    }

    // --- 2. INLINE EDIT (RENAME) ---
    document.querySelectorAll('.btn-edit-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            tr.querySelector('.tag-name-display').classList.add('d-none');
            tr.querySelector('.tag-name-edit').classList.remove('d-none');
            tr.querySelector('input').focus();
            this.disabled = true; // Disable edit button while editing
        });
    });

    document.querySelectorAll('.btn-cancel-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            tr.querySelector('.tag-name-display').classList.remove('d-none');
            tr.querySelector('.tag-name-edit').classList.add('d-none');
            tr.querySelector('.btn-edit-toggle').disabled = false;
        });
    });

    document.querySelectorAll('.btn-save-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            const id = tr.dataset.id;
            const newName = tr.querySelector('input').value;
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('tag_name', newName);

            fetch(`${baseUrl}?module=Media&controller=MediaTags&action=update`, {
                method: 'POST', body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) window.location.reload();
                else alert(data.error);
            });
        });
    });

    // --- 3. DELETE & MIGRATE FLOW ---
    const deleteModalEl = document.getElementById('deleteTagModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    let currentDeleteId = null;

    document.querySelectorAll('.btn-delete-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            currentDeleteId = tr.dataset.id;
            const name = tr.dataset.name;

            // Reset Modal State
            document.getElementById('modalTagName').textContent = name;
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');
            
            // Fyld dropdown til Step 2 (ekskluder det tag vi sletter)
            const select = document.getElementById('migrateTargetSelect');
            select.innerHTML = '';
            document.querySelectorAll('tr[data-id]').forEach(row => {
                if (row.dataset.id !== currentDeleteId) {
                    const opt = document.createElement('option');
                    opt.value = row.dataset.id;
                    opt.textContent = row.dataset.name;
                    select.appendChild(opt);
                }
            });

            deleteModal.show();
        });
    });

    // Step 1: Delete Simple (No migrate)
    document.getElementById('btnDeleteSimple').addEventListener('click', () => {
        executeDelete(currentDeleteId, null);
    });

    // Step 1 -> Step 2
    document.getElementById('btnGoToStep2').addEventListener('click', () => {
        // Tjek om der er andre tags at migrere til
        const select = document.getElementById('migrateTargetSelect');
        if (select.options.length === 0) {
            alert('There are no other tags to migrate to. You must delete simply.');
            return;
        }
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.remove('d-none');
    });

    // Step 2 -> Step 1 (Back)
    document.getElementById('btnBackToStep1').addEventListener('click', () => {
        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step1').classList.remove('d-none');
    });

    // Step 2: Migrate & Delete
    document.getElementById('btnMigrateAndDelete').addEventListener('click', () => {
        const targetId = document.getElementById('migrateTargetSelect').value;
        if(targetId) {
            executeDelete(currentDeleteId, targetId);
        }
    });

    function executeDelete(id, migrateToId) {
        const formData = new FormData();
        formData.append('id', id);
        if (migrateToId) {
            formData.append('migrate_to_id', migrateToId);
        }

        fetch(`${baseUrl}?module=Media&controller=MediaTags&action=delete`, {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
});