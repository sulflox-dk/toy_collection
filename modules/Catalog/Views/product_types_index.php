<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Product Types</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-header bg-white fw-bold">Add Product Type</div>
            <div class="card-body">
                <form id="createTypeForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Type Name</label>
                        <input type="text" class="form-control" name="type_name" placeholder="e.g. Action Figure" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Create Type</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Existing Types</span>
            </div>
            
            <div class="p-2 border-bottom bg-light">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchName" class="form-control" placeholder="Search types...">
                </div>
            </div>

            <div class="card-body p-0" id="typeGridContainer">
                <?php 
                    $data = $initialData['data']; 
                    $pages = $initialData['pages']; 
                    $current_page = $initialData['current_page'];
                    include __DIR__ . '/product_types_grid.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTypeForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Type Name</label>
                        <input type="text" class="form-control" name="type_name" id="edit_name" required>
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

<div class="modal fade" id="deleteTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">You are about to delete: <strong id="del_name"></strong></p>
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Assigned to <strong id="del_usage_count">0</strong> Master Toys.
                </div>
                
                <div id="step1">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" id="btnGoToStep2">
                            <i class="fas fa-exchange-alt me-2"></i> Move toys to another Type
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeleteSimple">
                            <i class="fas fa-trash-alt me-2"></i> Delete (Remove type from toys)
                        </button>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <p class="text-muted">Select new type:</p>
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