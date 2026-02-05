<?php
// Forbered data til JavaScript (Items)
$jsonItems = json_encode($toy['items'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
$isEdit = !empty($toy['id']);

// Forbered subjects med fuld data til JS søgning
$jsonSubjects = json_encode($subjects ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white">
    <h5 class="modal-title">
        <i class="fas fa-robot me-2"></i><?= $isEdit ? 'Edit Master Toy' : 'Add New Master Toy' ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form id="masterToyForm">
    <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $toy['id'] ?>"><?php endif; ?>
    
    <div class="modal-body p-4 modal-body-custom bg-light">
        
        <h6 class="section-label">General Toy Information</h6>
        
        <div class="card form-section-card shadow-sm border-0 mb-4">
            <div class="card-body">
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Universe</label>
                        <select class="form-select" id="master_toy_universe_id" name="universe_id_display" required>
                            <option value="">Select Universe...</option>
                            <?php foreach($universes as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($selected_universe_id == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Manufacturer</label>
                        <select class="form-select" id="master_toy_manufacturer_id" required>
                            <option value="">Select Manufacturer...</option>
                            <?php 
                            // Tjek om vi skal auto-vælge (hvis der kun er 1 item i listen)
                            $autoSelectMan = count($manufacturers) === 1; 
                            
                            foreach($manufacturers as $m): 
                                $isSelected = ($toy && $toy['manufacturer_id'] == $m['id']) || $autoSelectMan;
                            ?>
                                <option value="<?= $m['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Toy Line</label>
                        <select class="form-select" id="master_toy_toy_line_id" name="line_id" required>
                            <option value="">Select Manufacturer first...</option>
                            <?php 
                            // Tjek om vi skal auto-vælge
                            $autoSelectLine = count($lines) === 1;

                            foreach($lines as $l): 
                                $isSelected = ($toy && $toy['line_id'] == $l['id']) || $autoSelectLine;
                            ?>
                                <option value="<?= $l['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($l['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

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
                    <div class="col-md-6">
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
            <h6 class="section-label mb-0">Included Items (Parts/Figures)</h6>
            <span class="badge bg-secondary" id="itemCountBadge">0 item(s)</span>
        </div>

        <div id="itemsContainer" class="vstack gap-2" 
             data-items='<?= $jsonItems ?>' 
             data-subjects='<?= $jsonSubjects ?>'>
        </div>
        

        <div class="row g-2 mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-outline-dark w-100 py-2 border-dashed" onclick="MasterToyMgr.addItem()">
                    <i class="fas fa-plus me-2"></i> Add Single Item
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-outline-dark w-100 py-2 border-dashed" onclick="MasterToyMgr.openMultiAdd()">
                    <i class="fas fa-list-check me-2"></i> Add Multiple Items
                </button>
            </div>
        </div>

        <div id="multiAddOverlay" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-light" style="z-index: 1060;">
            <div class="d-flex flex-column h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Select Items to Add</h5>
                    <button type="button" class="btn-close" onclick="MasterToyMgr.closeMultiAdd()"></button>
                </div>
                
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="multiAddSearch" class="form-control" placeholder="Search subjects (e.g. 'Darth Vader')..." onkeyup="MasterToyMgr.filterMultiList(this.value)">
                </div>

                <div class="flex-grow-1 border rounded bg-white overflow-auto p-2" id="multiAddList">
                    <div class="text-center text-muted mt-5">Type to search for subjects...</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="text-muted small" id="multiAddCount">0 selected</span>
                    <button type="button" class="btn btn-success px-4" onclick="MasterToyMgr.addSelectedItems()">
                        <i class="fas fa-plus me-2"></i>Add Checked
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="modal-footer bg-light justify-content-between">
        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark px-4" onclick="MasterToyMgr.submitForm()">
            <?= $isEdit ? 'Save Changes' : 'Create Toy and Continue' ?>
        </button>
    </div>
</form>

<template id="itemRowTemplate">
    <div class="card mb-2 item-row border-0 shadow-sm" style="background-color: #fff; border: 1px solid #dee2e6;">
        <div class="card-body p-3 position-relative">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 small remove-row" aria-label="Remove" onclick="MasterToyMgr.removeItem(this)" style="font-size: 0.7rem; z-index: 5;"></button>
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Subject</label>
                    <input type="hidden" class="input-subject-id" name="items[UID][subject_id]">
                    <div class="subject-selector-wrapper position-relative">
                        <div class="subject-display-card d-flex align-items-center border rounded px-2 py-2 bg-white" 
                             onclick="MasterToyMgr.toggleSearch(this)"
                             style="cursor: pointer; min-height: 38px;">
                            <div class="me-2 text-muted d-flex align-items-center justify-content-center" style="width: 24px;">
                                <i class="fas fa-user-circle fs-5 subject-icon"></i>
                            </div>
                            <div class="flex-grow-1 lh-1">
                                <div class="subject-name fw-medium small text-truncate">Select Subject...</div>
                                <div class="subject-meta text-muted" style="font-size: 0.7rem; display: none;"></div>
                            </div>
                            <i class="fas fa-chevron-down text-muted small ms-2"></i>
                        </div>
                        <div class="subject-search-dropdown position-absolute w-100 bg-white border rounded shadow-sm mt-1 d-none" style="z-index: 1050; top: 100%;">
                            <div class="p-2 border-bottom bg-light">
                                <input type="text" class="form-control form-control-sm search-input" 
                                       placeholder="Type to search..." 
                                       onkeyup="MasterToyMgr.filterSubjects(this)"
                                       autocomplete="off">
                            </div>
                            <div class="results-list overflow-auto" style="max-height: 380px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Variant / Note</label>
                    <input type="text" class="form-control form-control-sm input-variant" name="items[UID][variant_description]" placeholder="e.g. Red Cape">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Qty</label>
                    <input type="number" class="form-control form-control-sm input-qty" name="items[UID][quantity]" value="1" min="1">
                </div>
            </div>
        </div>
    </div>
</template>