<?php
// Bestem om vi retter eller opretter
$isEdit = isset($mode) && $mode === 'edit';
$action = $isEdit ? 'update' : 'create';
$toyData = $toy ?? []; // Tomt array hvis vi opretter ny
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
                    <div class="col-md-6">
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

                    <div class="col-md-6">
                        <label class="form-label small text-muted">Manufacturer</label>
                        <select class="form-select" name="manufacturer_id" id="selectManufacturer" <?= $isEdit ? '' : 'disabled' ?> required>
                            <?php if($isEdit): ?>
                                <?php foreach($manufacturers as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $toyData['manufacturer_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option><?= isset($selected_universe) && $selected_universe ? 'Loading manufacturers...' : 'Select Universe first...' ?></option>
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

                    <div class="col-md-6">
                        <label class="form-label small text-muted">Toy (Master Definition)</label>
                        <select class="form-select" name="master_toy_id" id="selectMasterToy" <?= $isEdit ? '' : 'disabled' ?> required>
                            <?php if($isEdit): ?>
                                <?php foreach($masterToys as $mt): ?>
                                    <option value="<?= $mt['id'] ?>" <?= $mt['id'] == $toyData['master_toy_id'] ? 'selected' : '' ?>><?= htmlspecialchars($mt['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option>Select Line first...</option>
                            <?php endif; ?>
                        </select>
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
            <span class="badge item-count-badge" id="itemCountBadge"><?= isset($childItems) ? count($childItems) : 0 ?> items</span>
        </div>
        
        <div id="childItemsContainer">
            <?php if($isEdit && !empty($childItems)): ?>
                <?php foreach($childItems as $idx => $item): ?>
                    <div class="card mb-3 shadow-sm child-item-row">
                        <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $item['id'] ?>">
                        
                        <div class="card-header child-item-header d-flex justify-content-between align-items-center py-2 text-muted fw-normal">
                            <span>
                                <i class="fas fa-puzzle-piece me-2"></i>
                                <span class="text-uppercase text-secondary"><?= htmlspecialchars($item['part_name'] ?? 'Item') ?></span>
                                <span class="text-body-tertiary fw-normal">(<?= $item['part_type'] ?? 'Part' ?>)</span>
                            </span>
                            
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary px-2 delete-btn-general" 
                                    onclick="App.deleteToyItem(<?= $item['id'] ?>, this)" 
                                    title="Remove Item from Collection">
                                <i class="far fa-trash-alt me-1"></i> Delete
                            </button>
                        </div>

                        <div class="card-body p-3 bg-white">
                            
                            <div class="row g-3 mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small text-muted mb-1">Item</label>
                                    <select class="form-select form-select-sm item-part-select border-dark" name="items[<?= $idx ?>][master_toy_item_id]" required>
                                        <?php if(isset($availableParts)): ?>
                                            <?php foreach($availableParts as $part): ?>
                                                <option value="<?= $part['id'] ?>" <?= $part['id'] == $item['master_toy_item_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($part['name']) ?> (<?= $part['type'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="<?= $item['master_toy_item_id'] ?>" selected>Current Item (ID: <?= $item['master_toy_item_id'] ?>)</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="items[<?= $idx ?>][is_loose]" value="1" id="loose_<?= $idx ?>" <?= $item['is_loose'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="loose_<?= $idx ?>">Loose</label>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Condition</label>
                                    <select class="form-select form-select-sm" name="items[<?= $idx ?>][condition]">
                                        <option value="">Select...</option>
                                        <?php foreach($conditions as $c): ?>
                                            <option value="<?= $c ?>" <?= $item['condition'] == $c ? 'selected' : '' ?>><?= $c ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Authenticity</label>
                                    <select class="form-select form-select-sm" name="items[<?= $idx ?>][is_reproduction]">
                                        <option value="">Select...</option>
                                        <option value="Original" <?= ($item['is_reproduction'] ?? '') === 'Original' ? 'selected' : '' ?>>Original</option>
                                        <option value="Reproduction" <?= ($item['is_reproduction'] ?? '') === 'Reproduction' ? 'selected' : '' ?>>Repro</option>
                                        <option value="Unknown" <?= ($item['is_reproduction'] ?? '') === 'Unknown' ? 'selected' : '' ?>>Unknown</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25 my-2">

                            <h6 class="text-muted text-uppercase mb-2 small-section-header">Purchase Information (if different)</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Purchase Date</label>
                                    <input type="date" class="form-control form-control-sm" name="items[<?= $idx ?>][purchase_date]" value="<?= $item['purchase_date'] ?? '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Purchase Price</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">kr.</span>
                                        <input type="number" step="0.01" class="form-control" name="items[<?= $idx ?>][purchase_price]" placeholder="Default" value="<?= $item['purchase_price'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Source (Shop/Person)</label>
                                    <select class="form-select form-select-sm" name="items[<?= $idx ?>][source_id]">
                                        <option value="">Use General Source</option>
                                        <?php foreach($sources as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= ($item['source_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Acquisition Status</label>
                                    <select class="form-select form-select-sm" name="items[<?= $idx ?>][acquisition_status]">
                                        <option value="">Use General Status</option>
                                        <?php foreach($statuses as $st): ?>
                                            <option value="<?= $st ?>" <?= ($item['acquisition_status'] ?? '') == $st ? 'selected' : '' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small text-muted mb-1">Expected Arrival</label>
                                    <input type="date" class="form-control form-control-sm" name="items[<?= $idx ?>][expected_arrival_date]" value="<?= $item['expected_arrival_date'] ?? '' ?>">
                                </div>
                            </div>

                            <hr class="text-muted opacity-25 my-2">

                            <h6 class="text-muted text-uppercase mb-2 small-section-header">Collection Metadata</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Personal Toy ID</label>
                                    <input type="text" class="form-control form-control-sm" name="items[<?= $idx ?>][personal_item_id]" placeholder="e.g. SW-ACC-001" value="<?= htmlspecialchars($item['personal_item_id'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Storage</label>
                                    <select class="form-select form-select-sm" name="items[<?= $idx ?>][storage_id]">
                                        <option value="">Use General Storage</option>
                                        <?php foreach($storages as $loc): ?>
                                            <option value="<?= $loc['id'] ?>" <?= ($item['storage_id'] ?? '') == $loc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($loc['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1">Comments</label>
                                    <input type="text" class="form-control form-control-sm" name="items[<?= $idx ?>][user_comments]" placeholder="Specific notes..." value="<?= htmlspecialchars($item['user_comments'] ?? '') ?>">
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" class="btn btn-outline-dark w-100 py-2 border-dashed" id="btnAddItemRow">
            <i class="fas fa-plus me-2"></i> Add Item to this Set
        </button>

    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-link text-muted text-decoration-none me-auto" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-dark px-4"><?= $isEdit ? 'Save Changes' : 'Create Toy and Continue' ?></button>
    </div>

</form>

<template id="childRowTemplate">
    <div class="card mb-4 shadow-sm child-item-row">
        
        <div class="card-header child-item-header d-flex justify-content-between align-items-center py-2">
            <span class="text-uppercase"><i class="fas fa-puzzle-piece me-2"></i>Item #<span class="row-number">1</span></span>
            <button type="button" class="btn-close btn-sm remove-row-btn" aria-label="Remove"></button>
        </div>

        <div class="card-body p-3 bg-white">
            
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Item</label>
                    <select class="form-select form-select-sm item-part-select border-dark" name="items[INDEX][master_toy_item_id]" required>
                        <option value="">Select Master Toy first...</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="items[INDEX][is_loose]" value="1" id="loose_INDEX" checked>
                        <label class="form-check-label" for="loose_INDEX">Loose</label>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Condition</label>
                    <select class="form-select form-select-sm" name="items[INDEX][condition]">
                        <option value="">Select...</option>
                        <?php foreach($conditions as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Authenticity</label>
                    <select class="form-select form-select-sm" name="items[INDEX][is_reproduction]">
                        <option value="">Select...</option>
                        <option value="Original">Original</option>
                        <option value="Reproduction">Repro</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>
            </div>

            <hr class="text-muted opacity-25 my-2">

            <h6 class="text-muted text-uppercase mb-2 small-section-header">Purchase Information (if different)</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Purchase Date</label>
                    <input type="date" class="form-control form-control-sm" name="items[INDEX][purchase_date]">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Purchase Price</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">kr.</span>
                        <input type="number" step="0.01" class="form-control" name="items[INDEX][purchase_price]" placeholder="Default">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Source (Shop/Person)</label>
                    <select class="form-select form-select-sm" name="items[INDEX][source_id]">
                        <option value="">Use General Source</option>
                        <?php foreach($sources as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Acquisition Status</label>
                    <select class="form-select form-select-sm" name="items[INDEX][acquisition_status]">
                        <option value="">Use General Status</option>
                        <?php foreach($statuses as $st): ?>
                            <option value="<?= $st ?>"><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label small text-muted mb-1">Expected Arrival (if applicable)</label>
                    <input type="date" class="form-control form-control-sm" name="items[INDEX][expected_arrival_date]">
                </div>
            </div>

            <hr class="text-muted opacity-25 my-2">

            <h6 class="text-muted text-uppercase mb-2 small-section-header">Collection Metadata</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Personal Toy ID</label>
                    <input type="text" class="form-control form-control-sm" name="items[INDEX][personal_item_id]" placeholder="e.g. SW-ACC-001">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Storage</label>
                    <select class="form-select form-select-sm" name="items[INDEX][storage_id]">
                        <option value="">Use General Storage</option>
                        <?php foreach($storages as $loc): ?>
                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Comments</label>
                    <input type="text" class="form-control form-control-sm" name="items[INDEX][user_comments]" placeholder="Specific notes (e.g. 'Tip broken', 'Maling slidt')...">
                </div>
            </div>

        </div>
    </div>
</template>