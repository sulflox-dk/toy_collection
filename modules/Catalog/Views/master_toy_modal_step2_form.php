<?php
// Forbered data til JavaScript (Items)
$jsonItems = json_encode($toy['items'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white">
    <h5 class="modal-title">
        <i class="fas fa-robot me-2"></i><?= $toy ? 'Edit Master Toy' : 'Add New Master Toy' ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form id="masterToyForm">
    <?php if($toy): ?><input type="hidden" name="id" value="<?= $toy['id'] ?>"><?php endif; ?>
    
    <div class="modal-body p-4 modal-body-custom bg-light">
        
        <div class="text-muted text-uppercase mb-2 small-section-header">Product Details</div>
        
        <div class="card form-section-card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label small text-muted mb-1">Toy Name</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($toy['name'] ?? '') ?>" required placeholder="e.g. Luke Skywalker (Bespin Fatigues)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Release Year</label>
                        <input type="number" class="form-control" name="release_year" value="<?= $toy['release_year'] ?? '' ?>" placeholder="e.g. 1980">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Toy Line</label>
                        <select class="form-select" name="line_id" required>
                            <option value="">Select Line...</option>
                            <?php foreach($lines as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= ($toy && $toy['line_id'] == $l['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($l['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Product Type</label>
                        <select class="form-select" name="product_type_id">
                            <option value="">Select Type...</option>
                            <?php foreach($types as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($toy && $toy['product_type_id'] == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Entertainment Source</label>
                    <select class="form-select" name="entertainment_source_id">
                        <option value="">None / Select Source...</option>
                        <?php foreach($sources as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($toy && $toy['entertainment_source_id'] == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?> (<?= $s['type'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Wave Number</label>
                        <input type="text" class="form-control" name="wave_number" value="<?= htmlspecialchars($toy['wave_number'] ?? '') ?>" placeholder="e.g. 2">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Assortment / SKU</label>
                        <input type="text" class="form-control" name="assortment_sku" value="<?= htmlspecialchars($toy['assortment_sku'] ?? '') ?>" placeholder="e.g. 39640">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted text-uppercase small-section-header mb-0">Box Contents</div>
            <span class="badge bg-secondary rounded-pill" id="itemCountBadge">0</span>
        </div>

        <div id="itemsContainer" class="vstack gap-2" data-items='<?= $jsonItems ?>'>
            </div>
        
        <div id="emptyItemsMsg" class="text-center text-muted small py-4" style="display:none;">
            <i class="fas fa-box-open mb-2 fs-4 d-block opacity-50"></i>
            No contents defined.
        </div>

        <button type="button" class="btn btn-dashed w-100 py-2 mt-2" id="btnAddRow">
            <i class="fas fa-plus me-2"></i>Add Content Item
        </button>

    </div>

    <div class="modal-footer bg-light justify-content-between">
        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark px-4" onclick="MasterToyMgr.submitForm()">
            <?= $toy ? 'Update Toy' : 'Create & Add Photos' ?>
        </button>
    </div>
</form>

<template id="itemRowTemplate">
    <div class="card mb-2 border shadow-sm item-row">
        <div class="card-body p-3 position-relative">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 small remove-row" aria-label="Remove" style="font-size: 0.7rem;"></button>
            
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Subject</label>
                    <select class="form-select form-select-sm input-subject" name="items[INDEX][subject_id]" required>
                        <option value="">Select Subject...</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Variant / Note</label>
                    <input type="text" class="form-control form-control-sm input-variant" name="items[INDEX][variation_name]" placeholder="e.g. Red Cape">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Qty</label>
                    <input type="number" class="form-control form-control-sm input-qty" name="items[INDEX][quantity]" value="1" min="1">
                </div>
            </div>
        </div>
    </div>
</template>