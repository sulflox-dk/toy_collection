<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Data / Entertainment Sources</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-header bg-white fw-bold">Add Source</div>
            <div class="card-body">
                <form id="createSourceForm">
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
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. A New Hope" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-7">
                            <label class="form-label small text-muted">Type</label>
                            <select class="form-select" name="type">
                                <?php foreach($types as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label small text-muted">Year</label>
                            <input type="number" class="form-control" name="release_year" placeholder="1977">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Create Source</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Existing Sources</span>
                </div>
            
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
                        <select id="filterType" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <?php foreach($types as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchName" class="form-control" placeholder="Search sources...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0" id="sourcesGridContainer">
                <?php 
                    // Render det første grid server-side med det samme
                    // (Kræver at vi har adgang til Template objektet her, hvilket vi har i $this kontexten hvis vi var i controlleren)
                    // MEN da vi er i viewet, er det nemmest bare at inkludere filen manuelt og pakke data ud.
                    
                    // Vi snyder lidt og "pakker ud" de data vi fik fra controlleren ($initialData)
                    $data = $initialData['data']; 
                    $pages = $initialData['pages']; 
                    $current_page = $initialData['current_page'];
                    
                    include __DIR__ . '/sources_grid.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editSourceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSourceForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Universe</label>
                        <select class="form-select" name="universe_id" id="edit_universe_id" required>
                            <?php foreach($universes as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" id="edit_type">
                                <?php foreach($types as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Year</label>
                            <input type="number" class="form-control" name="release_year" id="edit_year">
                        </div>
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

<div class="modal fade" id="deleteSourceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">You are about to delete: <strong id="del_name"></strong></p>
                
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This source is linked to <strong id="del_usage_count">0</strong> Master Toys.
                </div>

                <div id="step1">
                    <p class="text-muted">How do you want to handle the linked toys?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" id="btnGoToStep2">
                            <i class="fas fa-exchange-alt me-2"></i> Move toys to another Source
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeleteSimple">
                            <i class="fas fa-trash-alt me-2"></i> Delete Source (Unlink toys)
                        </button>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <p class="text-muted">Select the new source for existing toys:</p>
                    <div class="mb-3">
                        <select class="form-select" id="migrateTargetSelect"></select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-dark" id="btnMigrateAndDelete">
                            Migrate Toys & Delete Source
                        </button>
                        <button type="button" class="btn btn-link text-muted" id="btnBackToStep1">Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>