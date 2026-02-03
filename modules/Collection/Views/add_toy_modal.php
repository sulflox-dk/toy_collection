<?php
// Bestem om vi retter eller opretter
$isEdit = isset($mode) && $mode === 'edit';
$action = $isEdit ? 'update' : 'create';
$toyData = $toy ?? []; // Tomt array hvis vi opretter ny

// 1. Forbered data til JS - RETTET: Nu hedder det jsonMasterToyItems
$jsonItems = json_encode($childItems ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
$jsonMasterToyItems = json_encode($availableParts ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white">
    <h5 class="modal-title"><i class="fas fa-box-open me-2"></i><?= $isEdit ? 'Edit Collection Entry' : 'Add to Collection' ?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form id="addToyForm" action="?module=Collection&controller=Toy&action=<?= $action ?>" method="POST">
    
    <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= $toyData['id'] ?>">
    <?php endif; ?>

    <div class="modal-body p-4 modal-body-custom">
        
        <h6 class="section-label">General Toy Information</h6>
        <div class="card form-section-card">
            <div class="card-body">
                <div class="row g-3">
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Universe</label>
                        <select class="form-select" name="universe_id" id="selectUniverse" required>
                            <option value="">Select...</option>
                            <?php foreach($universes as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (isset($selected_universe) && $u['id'] == $selected_universe) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small text-muted">Manufacturer</label>
                        <select class="form-select" name="manufacturer_id" id="selectManufacturer" <?= $isEdit ? '' : 'disabled' ?> required>
                            <?php if($isEdit): ?>
                                <?php foreach($manufacturers as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $toyData['manufacturer_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option><?= isset($selected_universe) && $selected_universe ? 'Loading...' : 'Select Universe first...' ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small text-muted">Toy Line</label>
                        <select class="form-select" name="line_id" id="selectLine" <?= $isEdit ? '' : 'disabled' ?> required>
                            <?php if($isEdit): ?>
                                <?php foreach($lines as $l): ?>
                                    <option value="<?= $l['id'] ?>" <?= $l['id'] == $toyData['line_id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option>Select Manufacturer first...</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small text-muted">Toy (Master Definition)</label>
                        
                        <input type="hidden" name="master_toy_id" id="inputMasterToyId" value="<?= $toyData['master_toy_id'] ?? '' ?>" required>

                        <?php
                        // PHP LOGIK: Find det valgte toy objekt og listen til JS
                        $currentToyObj = null;
                        if ($isEdit && !empty($masterToys) && !empty($toyData['master_toy_id'])) {
                            foreach ($masterToys as $mt) {
                                if ($mt['id'] == $toyData['master_toy_id']) {
                                    $currentToyObj = $mt;
                                    break;
                                }
                            }
                        }
                        $jsonSelectedToy = $currentToyObj ? json_encode($currentToyObj, JSON_HEX_APOS|JSON_HEX_QUOT) : '';
                        // Vi sender også hele listen med, så søgning virker med det samme uden API kald
                        $jsonAllToys = json_encode($masterToys ?? [], JSON_HEX_APOS|JSON_HEX_QUOT);
                        ?>

                        <div class="toy-selector-wrapper" id="masterToyWidgetWrapper"
                             data-selected-toy='<?= $jsonSelectedToy ?>'
                             data-all-toys='<?= $jsonAllToys ?>'>
                            
                            <div id="masterToyDisplayCard" class="toy-display-card <?= $isEdit ? '' : 'disabled' ?>">
                                <div class="toy-thumb-container">
                                    <i class="fas fa-box-open text-muted fa-2x" id="displayToyImgIcon"></i>
                                    <img src="" id="displayToyImg" class="toy-thumb-img d-none" alt="">
                                </div>
                                <div class="toy-info-container">
                                    <div class="toy-title" id="displayToyTitle">Select Line first...</div>
                                    <div class="toy-meta" id="displayToyMeta1"></div>
                                    <div class="toy-meta" id="displayToyMeta2"></div>
                                </div>
                                <div class="ms-3 text-muted">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>

                            <div id="masterToyOverlay" class="toy-search-overlay">
                                <div class="toy-search-header">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="inputToySearch" placeholder="Search toy name (e.g. Han Solo)..." autocomplete="off">
                                    </div>
                                </div>
                                <div id="toyResultsList" class="toy-results-list">
                                    </div>
                            </div>

                        </div>
                        </div>
                </div>
            </div>
        </div>

        <h6 class="section-label">Purchase Information</h6>
        <div class="card form-section-card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Purchase Date</label>
                        <input type="date" class="form-control" name="purchase_date" value="<?= $toyData['purchase_date'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Purchase Price</label>
                        <div class="input-group">
                            <span class="input-group-text">kr.</span>
                            <input type="number" step="0.01" class="form-control" name="purchase_price" placeholder="0.00" value="<?= $toyData['purchase_price'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Source (Shop/Person)</label>
                        <select class="form-select" name="source_id">
                            <option value="">Unknown / Other</option>
                            <?php foreach($sources as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($toyData['source_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Acquisition Status</label>
                        <select class="form-select" name="acquisition_status">
                            <?php foreach($statuses as $st): ?>
                                <option value="<?= $st ?>" <?= ($toyData['acquisition_status'] ?? 'In Hand') == $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="section-label">Collection Metadata</h6>
        <div class="card form-section-card">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label small text-muted">Overall Condition</label>
                        <select class="form-select" name="condition">
                            <option value="">Select Condition...</option>
                            <?php foreach($conditions as $c): ?>
                                <option value="<?= $c ?>" <?= ($toyData['condition'] ?? '') == $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label small text-muted">Completeness</label>
                        <select class="form-select" name="completeness_grade">
                            <option value="">Select Grade...</option>
                            <?php foreach($completeness as $cg): ?>
                                <option value="<?= $cg ?>" <?= ($toyData['completeness_grade'] ?? '') == $cg ? 'selected' : '' ?>><?= $cg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <div class="form-check mb-2"> 
                            <input class="form-check-input" type="checkbox" name="is_loose" value="1" id="parentIsLoose" <?= ($toyData['is_loose'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="parentIsLoose">Loose</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small text-muted">Storage Location</label>
                        <select class="form-select" name="storage_id">
                            <option value="">Unsorted</option>
                            <?php foreach($storages as $loc): ?>
                                <option value="<?= $loc['id'] ?>" <?= ($toyData['storage_id'] ?? '') == $loc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($loc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Personal ID (Optional)</label>
                        <input type="text" class="form-control" name="personal_toy_id" placeholder="e.g. SW-1978-001" value="<?= htmlspecialchars($toyData['personal_toy_id'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Comments</label>
                        <textarea class="form-control" name="user_comments" rows="2" placeholder="Any special notes..."><?= htmlspecialchars($toyData['user_comments'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="section-label mb-0">Included Items (Parts/Figures)</h6>
            <span class="badge item-count-badge" id="itemCountBadge">0 item(s)</span>
        </div>
        
        <div id="childItemsContainer" 
             data-items='<?= $jsonItems ?>' 
             data-master-toy-items='<?= $jsonMasterToyItems ?>'>
        </div>

        <button type="button" class="btn btn-outline-dark w-100 py-2 border-dashed" id="btnAddItemRow">
            <i class="fas fa-plus me-2"></i> Add Item to this Toy
        </button>

    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-link text-muted text-decoration-none me-auto" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-dark px-4"><?= $isEdit ? 'Save Changes' : 'Create Toy and Continue' ?></button>
    </div>

</form>

<template id="childRowTemplate">
    <div class="card child-item-row">
        <input type="hidden" name="items[INDEX][id]" class="item-db-id">
        
        <div class="card-header child-item-header d-flex justify-content-between align-items-center py-2 fw-normal">
            <span>
                <i class="fas fa-puzzle-piece me-2"></i>
                <span class="item-display-name text-uppercase">New Item</span>
                <span class="item-type-display text-body-tertiary fw-normal ms-1"></span>
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary px-2 delete-btn-general remove-row-btn">
                <i class="far fa-trash-alt me-1"></i> Delete
            </button>
        </div>
        <div class="card-body">
            
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Item</label>
                    <select class="form-select form-select-sm master-toy-item-select" name="items[INDEX][master_toy_item_id]" required>
                        <option value="">Select Master Toy first...</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <div class="form-check mb-1">
                        <input class="form-check-input input-loose" type="checkbox" name="items[INDEX][is_loose]" value="1" id="loose_INDEX" checked>
                        <label class="form-check-label" for="loose_INDEX">Loose</label>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Condition</label>
                    <select class="form-select form-select-sm input-condition" name="items[INDEX][condition]">
                        <option value="">Select...</option>
                        <?php foreach($conditions as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Authenticity</label>
                    <select class="form-select form-select-sm input-repro" name="items[INDEX][is_reproduction]">
                        <option value="">Select...</option>
                        <option value="Original">Original</option>
                        <option value="Reproduction">Repro</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 text-muted text-uppercase mb-2 small-section-header">Purchase Information (if different)</div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Purchase Date</label>
                    <input type="date" class="form-control form-control-sm input-p-date" name="items[INDEX][purchase_date]">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Purchase Price</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">kr.</span>
                        <input type="number" step="0.01" class="form-control input-price" name="items[INDEX][purchase_price]" placeholder="Default">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Source (Shop/Person)</label>
                    <select class="form-select form-select-sm input-source" name="items[INDEX][source_id]">
                        <option value="">Use General Source</option>
                        <?php foreach($sources as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Acquisition Status</label>
                    <select class="form-select form-select-sm input-acq" name="items[INDEX][acquisition_status]">
                        <option value="">Use General Status</option>
                        <?php foreach($statuses as $st): ?>
                            <option value="<?= $st ?>"><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label small text-muted mb-1">Expected Arrival (if applicable)</label>
                    <input type="date" class="form-control form-control-sm input-exp-date" name="items[INDEX][expected_arrival_date]">
                </div>
            </div>

            <div class="mt-4 text-muted text-uppercase mb-2 small-section-header">Collection Metadata</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Personal Toy ID</label>
                    <input type="text" class="form-control form-control-sm input-pers-id" name="items[INDEX][personal_item_id]" placeholder="e.g. SW-ACC-001">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Storage</label>
                    <select class="form-select form-select-sm input-storage" name="items[INDEX][storage_id]">
                        <option value="">Use General Storage</option>
                        <?php foreach($storages as $loc): ?>
                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Comments</label>
                    <input type="text" class="form-control form-control-sm input-comments" name="items[INDEX][user_comments]" placeholder="Specific notes (e.g. 'Tip broken', 'Maling slidt')...">
                </div>
            </div>

        </div>
    </div>
</template>