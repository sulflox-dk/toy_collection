<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Catalog / Toy Lines</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-header bg-white fw-bold">Add Toy Line</div>
            <div class="card-body">
                <form id="createLineForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Vintage Collection" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted">Universe</label>
                        <select class="form-select" name="universe_id" required>
                            <option value="">Select Universe...</option>
                            <?php foreach($universes as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Manufacturer</label>
                        <select class="form-select" name="manufacturer_id" required>
                            <option value="">Select Manufacturer...</option>
                            <?php foreach($manufacturers as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Scale</label>
                            <input type="text" class="form-control" name="scale" placeholder='e.g. 3.75"'>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Start Year</label>
                            <input type="number" class="form-control" name="era_start_year" placeholder="1977">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="chkShow" checked>
                        <label class="form-check-label small" for="chkShow">Show on Dashboard</label>
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100">Create Toy Line</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Existing Lines</div>
            
            <div class="p-2 border-bottom bg-light">
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
                        <select id="filterMan" class="form-select form-select-sm">
                            <option value="">All Manufacturers</option>
                            <?php foreach($manufacturers as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchName" class="form-control" placeholder="Search lines...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0" id="lineGridContainer">
                <?php 
                    $data = $initialData['data']; 
                    $pages = $initialData['pages']; 
                    $current_page = $initialData['current_page'];
                    include __DIR__ . '/toy_lines_grid.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editLineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Toy Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editLineForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Universe</label>
                            <select class="form-select" name="universe_id" id="edit_universe_id" required>
                                <?php foreach($universes as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Manufacturer</label>
                            <select class="form-select" name="manufacturer_id" id="edit_manufacturer_id" required>
                                <?php foreach($manufacturers as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Scale</label>
                            <input type="text" class="form-control" name="scale" id="edit_scale">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Start Year</label>
                            <input type="number" class="form-control" name="era_start_year" id="edit_year">
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="edit_show">
                        <label class="form-check-label" for="edit_show">Show on Dashboard</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteLineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Toy Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">You are about to delete: <strong id="del_name"></strong></p>
                
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Contains <strong id="del_usage_count">0</strong> Master Toys.
                </div>
                
                <div id="step1">
                    <p class="text-muted">Deleting this line will affect all linked toys. Migration is recommended.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" id="btnGoToStep2">
                            <i class="fas fa-exchange-alt me-2"></i> Move toys to another Line
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeleteSimple">
                            <i class="fas fa-trash-alt me-2"></i> Delete Line (DELETES TOYS!)
                        </button>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <p class="text-muted">Select target line:</p>
                    <select class="form-select mb-3" id="migrateTargetSelect"></select>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-dark" id="btnMigrateAndDelete">Migrate & Delete</button>
                        <button type="button" class="btn btn-link text-muted" id="btnBackToStep1">Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>