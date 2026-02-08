<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-dark">My Collection</h1>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-view-list" 
                    onclick="CollectionMgr.switchView('list')" title="List View">
                <i class="fas fa-list"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-view-cards" 
                    onclick="CollectionMgr.switchView('cards')" title="Grid View">
                <i class="fas fa-th"></i>
            </button>
        </div>
    <button class="btn btn-dark btn-sm" onclick="CollectionForm.openAddModal()">
        <i class="fas fa-plus me-1"></i> Add New
    </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3">
        
        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <select id="filterUniverse" class="form-select form-select-sm">
                    <option value="">All Universes</option>
                    <?php foreach($universes as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterManufacturer" class="form-select form-select-sm">
                    <option value="">All Manufacturers</option>
                    <?php foreach($manufacturers as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterLine" class="form-select form-select-sm">
                    <option value="">All Toy Lines</option>
                    <?php foreach($lines as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterProductType" class="form-select form-select-sm">
                    <option value="">All Product Types</option>
                    <?php foreach($productTypes as $pt): ?>
                        <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <select id="filterStorage" class="form-select form-select-sm">
                    <option value="">All Storage Units</option>
                    <?php foreach($storage_units as $su): ?>
                        <option value="<?= $su['id'] ?>"><?= htmlspecialchars($su['box_code'] . ' - ' . $su['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">Acquisition Status: All</option>
                    <?php foreach($statuses as $st): ?>
                        <option value="<?= $st ?>"><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterCompleteness" class="form-select form-select-sm">
                    <option value="">Completeness Grade: All</option>
                    <?php foreach($grades as $g): ?>
                        <option value="<?= $g ?>"><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterMissingParts" class="form-select form-select-sm">
                    <option value="">Missing Parts: All</option>
                    <option value="yes">Has Missing Parts</option>
                    <option value="no">100% Complete</option>
                </select>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-3">
                <select id="filterImage" class="form-select form-select-sm">
                    <option value="">Image: All</option>
                    <option value="has_image">My Photo Uploaded</option>
                    <option value="missing_image">No Photo (Stock Only)</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchCollection" class="form-control" placeholder="Search collection...">
                </div>
            </div>
            <div class="col-md-4"></div> <div class="col-md-2 d-grid">
                <button class="btn btn-sm btn-light border text-muted" onclick="CollectionMgr.resetFilters()">
                    <i class="fas fa-times me-1"></i> Reset
                </button>
            </div>
        </div>

    </div>

    <div class="card-body p-0" id="collectionGridContainer">
        <div class="p-5 text-center text-muted">
            <div class="spinner-border text-secondary mb-3" role="status"></div>
            <div>Loading collection...</div>
        </div>
    </div>
</div>