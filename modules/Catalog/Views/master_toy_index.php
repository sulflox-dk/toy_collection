<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Catalog / Master Toys</h1>
    <button class="btn btn-dark" onclick="MasterToyMgr.openUniverseSelect()">
        <i class="fas fa-plus me-2"></i> Add New Toy
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white p-3">
        <div class="row g-2">
            <div class="col-md-3">
                <select id="filterUniverse" class="form-select form-select-sm">
                    <option value="">All Universes</option>
                    <?php foreach($universes as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
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
                <select id="filterSource" class="form-select form-select-sm">
                    <option value="">All Entertainment Sources</option>
                    <?php foreach($sources as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['universe_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchName" class="form-control" placeholder="Search toys or items...">
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0" id="masterToyGridContainer">
        <?php 
            $data = $initialData['data']; 
            $pages = $initialData['pages']; 
            $current_page = $initialData['current_page'];
            // RETTET: Inkluderer master_toy_grid.php
            include __DIR__ . '/master_toy_grid.php'; 
        ?>
    </div>
</div>