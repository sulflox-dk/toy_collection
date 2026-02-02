<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manufacturers</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-header bg-white fw-bold">Add Manufacturer</div>
            <div class="card-body">
                <form id="createManForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Kenner" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="chkShow" checked>
                        <label class="form-check-label small" for="chkShow">Show on Dashboard</label>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Create Manufacturer</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Existing Manufacturers</span>
            </div>
            
            <div class="p-2 border-bottom bg-light">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchName" class="form-control" placeholder="Search manufacturers...">
                </div>
            </div>

            <div class="card-body p-0" id="manGridContainer">
                <?php 
                    // Pak data ud fra controlleren og inkluder gridet første gang
                    $data = $initialData['data']; 
                    $pages = $initialData['pages']; 
                    $current_page = $initialData['current_page'];
                    include __DIR__ . '/manufacturers_grid.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editManModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Manufacturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editManForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
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

<div class="modal fade" id="deleteManModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Manufacturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">You are about to delete: <strong id="del_name"></strong></p>
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Linked to <strong id="del_usage_count">0</strong> Toy Lines.
                </div>
                
                <div id="step1">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" id="btnGoToStep2">
                            <i class="fas fa-exchange-alt me-2"></i> Move lines to another Manufacturer
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeleteSimple">
                            <i class="fas fa-trash-alt me-2"></i> Delete (Orphan lines)
                        </button>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <p class="text-muted">Select new manufacturer:</p>
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