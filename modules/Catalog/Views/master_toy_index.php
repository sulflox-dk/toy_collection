<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-dark">Catalog / Master Toys</h1>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-view-list" 
                    onclick="MasterToyMgr.switchView('list')" title="List View">
                <i class="fas fa-list"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-view-cards" 
                    onclick="MasterToyMgr.switchView('cards')" title="Grid View">
                <i class="fas fa-th"></i>
            </button>
        </div>

        <button class="btn btn-dark btn-sm" onclick="MasterToyMgr.openUniverseSelect()">
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

        <div class="row g-2">
            <div class="col-md-3">
                <select id="filterSource" class="form-select form-select-sm">
                    <option value="">All Entertainment Sources</option>
                    <?php foreach($sources as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filterOwned" class="form-select form-select-sm">
                    <option value="">Status: All</option>
                    <option value="owned">Owned</option>
                    <option value="not_owned">Missing</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filterImage" class="form-select form-select-sm">
                    <option value="">Image: All</option>
                    <option value="has_image">Has Photo</option>
                    <option value="missing_image">No Photo</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class=\"input-group-text bg-white\"><i class=\"fas fa-search text-muted\"></i></span>
                    <input type="text" id="searchName" class="form-control" placeholder="Search...">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-sm btn-light border text-muted" onclick="MasterToyMgr.resetFilters()">
                    <i class="fas fa-times me-1"></i> Reset
                </button>
            </div>
        </div>

    </div>

    <div class="card-body p-0" id="masterToyGridContainer">
        <?php 
            $data = $initialData['data']; 
            $pages = $initialData['pages']; 
            $current_page = $initialData['current_page'];
            include __DIR__ . '/master_toy_grid.php'; 
        ?>
    </div>
</div>