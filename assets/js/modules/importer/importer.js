document.addEventListener('DOMContentLoaded', () => {
	const btnPreview = document.getElementById('btnPreview');
	const importUrl = document.getElementById('importUrl');
	const resultsGrid = document.getElementById('resultsGrid');
	const importResults = document.getElementById('importResults');
	const btnRunImport = document.getElementById('btnRunImport');

	let currentItems = [];

	// 1. Preview Logic
	btnPreview.addEventListener('click', async () => {
		const url = importUrl.value.trim();
		if (!url) return;

		importResults.classList.remove('d-none');
		UiHelper.showLoader('#resultsGrid');
		btnPreview.disabled = true;

		try {
			const formData = new FormData();
			formData.append('url', url);
			const apiUrl = `${SITE_URL}?module=Importer&controller=Importer&action=preview`;

			const response = await fetch(apiUrl, {
				method: 'POST',
				body: formData,
			});
			const result = await response.json();

			if (result.success) {
				currentItems = result.data;
				renderGrid(currentItems);
				document.getElementById('itemCount').textContent =
					currentItems.length;
				if (currentItems.length === 0) {
					resultsGrid.innerHTML = `<div class="alert alert-warning">No items found on this URL.</div>`;
				}
			} else {
				UiHelper.showError(result.error);
				resultsGrid.innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
			}
		} catch (error) {
			console.error(error);
			UiHelper.showError('System error: ' + error.message);
			resultsGrid.innerHTML = `<div class="alert alert-danger">System error. Check console.</div>`;
		} finally {
			btnPreview.disabled = false;
		}
	});

	// 2. Render Grid (NU I FULL WIDTH MED ALLE DATA)
	function renderGrid(items) {
		resultsGrid.innerHTML = '';

		items.forEach((item, index) => {
			let cardClass = 'border-success';
			let badge = '<span class="badge bg-success">NEW</span>';
			let actionHtml =
				'<div class="text-success"><i class="fas fa-check-circle"></i> Ready to Create</div>';

			if (item.status === 'conflict') {
				cardClass = 'border-warning';
				badge = '<span class="badge bg-warning text-dark">CONFLICT</span>';
				actionHtml = `<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Match: ${item.matchReason} (ID: ${item.existingId})</div>`;
			} else if (item.status === 'linked') {
				cardClass = 'border-info';
				badge = '<span class="badge bg-info">LINKED</span>';
				actionHtml = `<div class="text-info"><i class="fas fa-link"></i> Will update ID: ${item.existingId}</div>`;
			}

			// Billede logik
			let imgHtml = '';
			if (item.images && item.images.length > 0) {
				imgHtml = `<img src="${item.images[0]}" class="img-fluid rounded-start h-100" style="object-fit: contain; max-height: 300px; width: 100%; background: #f8f9fa;">`;
			} else {
				imgHtml = `<div class="d-flex align-items-center justify-content-center bg-light h-100" style="min-height: 200px;"><span class="text-muted">No Image</span></div>`;
			}

			// Formater Items liste
			let itemsHtml =
				'<span class="text-muted fst-italic">None detected</span>';
			if (item.items && item.items.length > 0) {
				itemsHtml = item.items
					.map((i) => `<span class="badge bg-secondary me-1">${i}</span>`)
					.join('');
			}

			const col = document.createElement('div');
			col.className = 'col-12 mb-4'; // Fuld bredde

			col.innerHTML = `
                <div class="card ${cardClass} shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-3 border-end">
                            ${imgHtml}
                        </div>
                        <div class="col-md-9">
                            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                                <div>
                                    ${badge} 
                                    <span class="ms-2 fw-bold text-muted small">${item.externalId}</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input item-select" type="checkbox" value="${index}" checked>
                                    <label class="form-check-label fw-bold">Import this item</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <h4 class="card-title text-primary mb-3">${item.name}</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tbody>
                                                <tr><td class="text-muted w-25">Year:</td>       <td><strong>${item.year || '-'}</strong></td></tr>
                                                <tr><td class="text-muted">Line:</td>       <td>${item.toyLine || '<span class="text-danger">Not found</span>'}</td></tr>
                                                <tr><td class="text-muted">Manuf.:</td>     <td>${item.manufacturer || '<span class="text-danger">Not found</span>'}</td></tr>
                                                <tr><td class="text-muted">Source:</td>     <td>${item.source_id ? 'Galactic Figures' : 'Unknown'}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tbody>
                                                <tr><td class="text-muted w-25">Wave:</td>       <td>${item.wave || '<span class="text-warning">Not found</span>'}</td></tr>
                                                <tr><td class="text-muted">SKU:</td>        <td>${item.assortmentSku || '<span class="text-warning">Not found</span>'}</td></tr>
                                                <tr><td class="text-muted">Status:</td>     <td>${actionHtml}</td></tr>
                                                <tr><td class="text-muted">URL:</td>        <td><a href="${item.externalUrl}" target="_blank" class="small">Link <i class="fas fa-external-link-alt"></i></a></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-uppercase text-muted fw-bold">Included Accessories (Items)</small>
                                    <div class="mt-1">${itemsHtml}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
			resultsGrid.appendChild(col);
		});
	}

	// 3. Run Import Logic (Uændret)
	if (btnRunImport) {
		btnRunImport.addEventListener('click', async () => {
			const checkboxes = document.querySelectorAll('.item-select:checked');
			const selectedIndices = Array.from(checkboxes).map((cb) => cb.value);
			const itemsToImport = selectedIndices.map(
				(index) => currentItems[index],
			);

			if (itemsToImport.length === 0) {
				alert('No items selected');
				return;
			}

			if (!confirm(`Import ${itemsToImport.length} items?`)) return;

			btnRunImport.disabled = true;
			const originalText = btnRunImport.innerHTML;
			btnRunImport.innerHTML =
				'<i class="fas fa-spinner fa-spin"></i> Importing...';

			try {
				const formData = new FormData();
				formData.append('items', JSON.stringify(itemsToImport));
				const apiUrl = `${SITE_URL}?module=Importer&controller=Importer&action=runImport`;

				const response = await fetch(apiUrl, {
					method: 'POST',
					body: formData,
				});
				const result = await response.json();

				if (result.success) {
					UiHelper.showSuccess(
						`Successfully imported ${result.count} items!`,
					);
					importResults.classList.add('d-none');
					importUrl.value = '';
					currentItems = [];
				} else {
					UiHelper.showError(result.error || 'Import failed');
				}
			} catch (error) {
				console.error(error);
				UiHelper.showError('System error during import');
			} finally {
				btnRunImport.disabled = false;
				btnRunImport.innerHTML = originalText;
			}
		});
	}
});
