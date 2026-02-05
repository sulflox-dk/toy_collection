<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Collection / Storage</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-header bg-white fw-bold" id="formTitle">
                Add Storage Unit
            </div>
            <div class="card-body">
                <form id="storageForm">
                    <input type="hidden" name="id" id="storageId">
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted">Box Code / ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control" name="box_code" id="boxCode" required placeholder="e.g. B001">
                        </div>
                        <div class="form-text small">Unique identifier for the box.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" name="name" id="storageName" required placeholder="e.g. Large Plastic Bin">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Location / Room</label>
                        <input type="text" class="form-control" name="location_room" id="storageLocation" placeholder="e.g. Basement - Shelf 2">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Description</label>
                        <textarea class="form-control" name="description" id="storageDescription" rows="3" placeholder="Notes about content or box type..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save me-2"></i>Save Unit
                        </button>
                        <button type="button" class="btn btn-outline-secondary d-none" id="btnCancel" onclick="StorageMgr.resetForm()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Existing Storage Units</div>
            
            <div class="p-2 border-bottom bg-light">
                <div class="row g-2">
                    <div class="col-md-12">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchStorage" class="form-control" placeholder="Search box code, name or location...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0" id="storageGrid">
                <div class="p-5 text-center text-muted">
                    <div class="spinner-border text-secondary mb-3" role="status"></div>
                    <div>Loading storage units...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editStorageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Storage Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <form id="editStorageForm">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted">Box Code / ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control" name="box_code" id="edit_box_code" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Location / Room</label>
                        <input type="text" class="form-control" name="location_room" id="edit_location_room">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark px-4" onclick="StorageMgr.update()">Update Storage Unit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteStorageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Storage Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-4 lead">You are about to delete: <strong id="del_name"></strong></p>
                
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        Contains <strong id="del_usage_count">0</strong> Toys.
                    </div>
                </div>
                
                <div id="step1">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark py-2" id="btnGoToStep2">
                            <i class="fas fa-exchange-alt me-2"></i> Move toys to another Box
                        </button>
                        <button type="button" class="btn btn-outline-danger py-2" id="btnDeleteSimple">
                            <i class="fas fa-trash-alt me-2"></i> Empty Box & Delete
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">"Empty Box" removes the items from this storage unit but keeps the toys.</small>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <label class="form-label fw-bold">Select destination box:</label>
                    <select class="form-select form-select-lg mb-4" id="migrateTargetSelect"></select>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-dark py-2" id="btnMigrateAndDelete">
                            <i class="fas fa-check me-2"></i>Migrate & Delete
                        </button>
                        <button type="button" class="btn btn-link text-muted" id="btnBackToStep1">Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>