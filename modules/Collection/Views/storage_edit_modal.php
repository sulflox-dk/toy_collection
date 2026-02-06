<div class="modal-header bg-dark text-white">
    <h5 class="modal-title">
        <i class="fas fa-edit me-2"></i>Edit Storage Unit
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body bg-light p-4">
    <form id="editStorageForm">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        
        <div class="mb-3">
            <label class="form-label small text-muted">Box Code / ID</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-barcode text-muted"></i></span>
                <input type="text" class="form-control" name="box_code" value="<?= htmlspecialchars($data['box_code']) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">Location / Room</label>
            <input type="text" class="form-control" name="location_room" value="<?= htmlspecialchars($data['location_room'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">Description</label>
            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
        </div>
    </form>
</div>

<div class="modal-footer bg-light">
    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-dark px-4" onclick="StorageMgr.update()">
        Update Storage Unit
    </button>
</div>