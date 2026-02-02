<div class="modal-header border-bottom-0 pb-0">
    <h5 class="modal-title">Media Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-7 text-center bg-light rounded d-flex align-items-center justify-content-center" style="min-height: 300px;">
            <img src="<?= $media['file_path'] ?>" class="img-fluid" style="max-height: 400px; object-fit: contain;" alt="Media">
        </div>

        <div class="col-md-5">
            <div class="mb-4 mt-3 mt-md-0">
                <label class="small text-muted text-uppercase fw-bold">Metadata</label>
                <div class="mb-2">
                    <strong>Filename:</strong> <span class="text-break"><?= htmlspecialchars($media['original_filename'] ?? basename($media['file_path'])) ?></span>
                </div>
                <div class="mb-2">
                    <strong>Uploaded:</strong> <?= date('d. M Y', strtotime($media['uploaded_at'])) ?>
                </div>
                
                <?php if(!empty($media['user_comment'])): ?>
                    <div class="mb-2">
                        <strong>Comment:</strong> 
                        <span class="fst-italic text-muted">"<?= htmlspecialchars($media['user_comment']) ?>"</span>
                    </div>
                <?php endif; ?>

                <?php if(!empty($media['tags'])): ?>
                    <div class="mt-2">
                        <?php foreach($media['tags'] as $tag): ?>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($tag['tag_name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <div>
                <label class="small text-muted text-uppercase fw-bold mb-2">Connected To</label>
                
                <?php if(empty($media['connections'])): ?>
                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-exclamation-triangle me-1"></i> No connections found. This is an orphan file.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush small" id="connectionList">
                        <?php foreach($media['connections'] as $conn): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <?php if(strpos($conn['context'], 'catalog') !== false): ?>
                                        <i class="fas fa-book text-primary me-2" title="Catalog"></i>
                                    <?php else: ?>
                                        <i class="fas fa-box-open text-success me-2" title="Collection"></i>
                                    <?php endif; ?>
                                    
                                    <strong><?= htmlspecialchars($conn['name']) ?></strong>
                                    <br>
                                    <span class="text-muted" style="font-size: 0.85em;"><?= $conn['type'] ?></span>
                                </div>
                                <button class="btn btn-sm btn-outline-danger border-0" 
                                        onclick="MediaLib.unlinkConnection(<?= $media['id'] ?>, '<?= $conn['context'] ?>', <?= $conn['id'] ?>, this)"
                                        title="Remove link">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer bg-light justify-content-between">
    <button type="button" class="btn btn-outline-danger" onclick="MediaLib.deleteSingle(<?= $media['id'] ?>)">
        <i class="fas fa-trash-alt me-2"></i>Permanently Delete File
    </button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>