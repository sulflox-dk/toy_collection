<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Media Tags</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Create New Tag</div>
            <div class="card-body">
                <form id="createTagForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Tag Name</label>
                        <input type="text" class="form-control" name="tag_name" placeholder="e.g. Box Art" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Create Tag</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Existing Tags</span>
                <span class="badge bg-secondary"><?= count($tags) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Name</th>
                                <th class="text-center">Usage</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tags as $tag): ?>
                                <tr data-id="<?= $tag['id'] ?>" data-name="<?= htmlspecialchars($tag['tag_name']) ?>">
                                    <td class="ps-3 fw-medium">
                                        <span class="tag-name-display"><?= htmlspecialchars($tag['tag_name']) ?></span>
                                        <div class="input-group input-group-sm tag-name-edit d-none">
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($tag['tag_name']) ?>">
                                            <button class="btn btn-success btn-save-edit"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-outline-secondary btn-cancel-edit"><i class="fas fa-times"></i></button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?= $tag['usage_count'] ?> files</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-secondary btn-edit-toggle" title="Rename">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-delete-modal" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($tags)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No tags found. Create one!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">You are about to delete the tag: <strong id="modalTagName"></strong></p>
                
                <div id="step1">
                    <p class="text-muted">Do you want to delete this tag without migrating its connections?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger" id="btnDeleteSimple">
                            Yes, delete tag and remove connections
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="btnGoToStep2">
                            No, I want to migrate connections
                        </button>
                    </div>
                </div>

                <div id="step2" class="d-none">
                    <p class="text-muted">Select the tag you want to move existing connections to:</p>
                    <div class="mb-3">
                        <select class="form-select" id="migrateTargetSelect">
                            </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-dark" id="btnMigrateAndDelete">
                            Migrate Connections & Delete Tag
                        </button>
                        <button type="button" class="btn btn-link text-muted" id="btnBackToStep1">Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>