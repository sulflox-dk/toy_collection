<div class="modal-header bg-dark text-white">
    <h5 class="modal-title"><i class="fas fa-camera me-2"></i>Add Photos</h5>
</div>

<div class="modal-body p-4 bg-light" id="media-upload-container" data-tags='<?= json_encode($available_tags ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
    
    <div class="alert alert-success border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <strong>Success!</strong> Toy and item(s) created.
    </div>

    <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">General Toy Information</h6>
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($toy['toy_name']) ?></h5>
                </div>
                <div>
                    <label class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Photo
                        <input type="file" class="d-none upload-input" data-context="collection_parent" data-id="<?= $toy['id'] ?>" accept="image/*" multiple>
                    </label>
                </div>
            </div>
            
            <div class="vstack gap-3" id="preview-parent-<?= $toy['id'] ?>"></div>
        </div>
    </div>

    <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Included Items (Parts/Figures)</h6>
    
    <?php foreach($items as $item): ?>
        <div class="card shadow-sm mb-2 border-0">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="min-width: 50%;">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 me-2"><?= htmlspecialchars($item['subject_name']) ?></h5>
                            <span class="text-muted small fw-normal">(<?= $item['type'] ?>)</span>
                        </div>
                        <?php if($item['variant_description']): ?>
                            <div class="small text-muted mt-1"><?= htmlspecialchars($item['variant_description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Photo
                            <input type="file" class="d-none upload-input" data-context="collection_child" data-id="<?= $item['id'] ?>" accept="image/*" multiple>
                        </label>
                    </div>
                </div>

                <div class="vstack gap-3" id="preview-child-<?= $item['id'] ?>"></div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<div class="modal-footer bg-light">
    <a href="?module=dashboard" class="btn btn-link text-muted text-decoration-none me-auto">Finish without adding photos</a>
    <a href="?module=dashboard" class="btn btn-dark px-4">Finish and Go to Dashboard</a>
</div>