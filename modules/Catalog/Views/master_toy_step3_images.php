<?php
// Bestem mode (Create = Wizard flow efter oprettelse, Edit = Direkte adgang)
$isCreate = isset($mode) && $mode === 'create';

// Forbered data til JavaScript
$tagsJson = json_encode($available_tags ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
$mediaJson = json_encode([
    'parent' => $toy['images'] ?? [], 
    'items' => $items ?? []
], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white">
    <h5 class="modal-title">
        <i class="fas fa-camera me-2"></i><?= $isCreate ? 'Add Photos' : 'Manage Photos' ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body p-4 bg-light modal-body-custom" 
     id="media-upload-container" 
     data-tags='<?= $tagsJson ?>'
     data-existing-media='<?= $mediaJson ?>'>
    
    <?php if($isCreate): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> <strong>Success!</strong> Master Toy created. Now let's add some photos.
        </div>
    <?php endif; ?>

    <h6 class="section-label">Master Toy (Packaging / Box)</h6>
    <div class="card form-section-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($toy['name']) ?></h5>
                    <span class="text-muted small">Main Product Entry</span>
                </div>
                <div>
                    <label class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Photo
                        <input type="file" class="d-none upload-input" 
                               data-context="catalog_parent" 
                               data-id="<?= $toy['id'] ?>" 
                               accept="image/*" multiple>
                    </label>
                </div>
            </div>
            
            <div class="vstack gap-3" id="preview-parent-<?= $toy['id'] ?>"></div>
        </div>
    </div>

    <h6 class="section-label">Included Items (Loose Figures/Parts)</h6>
    
    <?php if(empty($items)): ?>
        <div class="alert alert-light border text-muted text-center small">
            No items defined for this toy yet.
        </div>
    <?php endif; ?>

    <?php foreach($items as $item): ?>
        <div class="card form-section-card mb-2">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 me-2"><?= htmlspecialchars($item['subject_name']) ?></h5>
                            <span class="text-muted small fw-normal">(<?= htmlspecialchars($item['subject_type']) ?>)</span>
                        </div>
                        <?php if(!empty($item['variant_description'])): ?>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-tag me-1"></i><?= htmlspecialchars($item['variant_description']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Photo
                            <input type="file" class="d-none upload-input" 
                                   data-context="catalog_child" 
                                   data-id="<?= $item['id'] ?>" 
                                   accept="image/*" multiple>
                        </label>
                    </div>
                </div>

                <div class="vstack gap-3" id="preview-child-<?= $item['id'] ?>"></div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<div class="modal-footer bg-light">
    <?php if($isCreate): ?>
        <a href="#" onclick="MasterToyMgr.loadPage(1); bootstrap.Modal.getInstance(document.getElementById('appModal')).hide(); return false;" class="btn btn-link text-muted text-decoration-none me-auto">Finish without adding photos</a>
        <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal" onclick="MasterToyMgr.loadPage(1)">Finish</button>
    <?php else: ?>
        <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal">Close</button>
    <?php endif; ?>
</div>