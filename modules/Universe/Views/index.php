<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Universes</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
            <div class="card-header bg-white fw-bold">Add Universe</div>
            <div class="card-body">
                <form id="createUniverseForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Marvel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Slug (URL friendly)</label>
                        <input type="text" class="form-control" name="slug" placeholder="e.g. marvel">
                        <div class="form-text small">Used for file names and urls.</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="99">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="chkShow" checked>
                                <label class="form-check-label small" for="chkShow">Dashboard</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Create Universe</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Existing Universes</span>
                <span class="badge bg-secondary"><?= count($universes) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 5%">#</th>
                                <th style="width: 35%">Name / Slug</th>
                                <th class="text-center" style="width: 15%">Dash.</th>
                                <th class="text-center" style="width: 25%">Content</th>
                                <th class="text-end pe-3" style="width: 20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($universes as $u): ?>
                                <tr data-id="<?= $u['id'] ?>" data-json='<?= json_encode($u, JSON_HEX_APOS) ?>'>
                                    <td class="ps-3 text-muted small"><?= $u['sort_order'] ?></td>
                                    <td>
                                        <div class="fw-bold name-display"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="small text-muted slug-display"><?= htmlspecialchars($u['slug']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php if($u['show_on_dashboard']): ?>
                                            <i class="fas fa-check text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times text-muted"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center small">
                                        <span class="badge bg-light text-dark border me-1" title="Toy Lines">
                                            <i class="fas fa-list me-1"></i> <?= $u['line_count'] ?>
                                        </span>
                                        <span class="badge bg-light text-dark border" title="Entertainment Sources">
                                            <i class="fas fa-film me-1"></i> <?= $u['source_count'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-secondary btn-edit" title="Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUniverseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Universe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUniverseForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" id="edit_slug" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" id="edit_sort">
                        </div>
                        <div class="col-6 pt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="edit_show">
                                <label class="form-check-label" for="edit_show">Show on Dashboard</label>
                            </div>
                        </div>
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

<div class="modal fade" id="deleteUniverseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Universe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">You are about to delete: <strong id="del_name"></strong></p>
                
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This universe has <strong id="del_line_count">0</strong> Toy Lines and <strong id="del_source_count">0</strong> Entertainment Sources.
                </div>

                <div id="step1">
                    <p class="text-muted">How do you want to handle the associated data?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" id="btnGoToStep2">
                            <i class="fas fa-exchange-alt me-2"></i> Move data to another Universe
                        </button>
                        <button type="button" class="btn btn-danger" id="btnDeleteSimple">
                            <i class="fas fa-trash-alt me-2"></i> Delete Universe (Orphan/Delete data)
                        </button>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <p class="text-muted">Select the new home for existing Toy Lines and Sources:</p>
                    <div class="mb-3">
                        <select class="form-select" id="migrateTargetSelect"></select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-dark" id="btnMigrateAndDelete">
                            Migrate Data & Delete Universe
                        </button>
                        <button type="button" class="btn btn-link text-muted" id="btnBackToStep1">Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>