<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/?module=Collection&controller=Showcase&action=index" class="text-decoration-none text-muted">Collection</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($toy['toy_name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-7">
            <?php if(!empty($gallery)): ?>
                <div class="mb-3 bg-light rounded d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="height: 500px;">
                    <img id="mainImage" src="<?= htmlspecialchars($gallery[0]['file_path']) ?>" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                </div>
                <div class="d-flex gap-2 overflow-auto py-2" style="white-space: nowrap;">
                    <?php foreach($gallery as $img): ?>
                        <img src="<?= htmlspecialchars($img['file_path']) ?>" 
                             class="rounded border cursor-pointer opacity-75 hover-opacity-100" 
                             style="height: 80px; width: 80px; object-fit: cover; cursor: pointer;"
                             onclick="document.getElementById('mainImage').src = this.src">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="height: 500px;">
                    <div class="text-center">
                        <i class="fas fa-camera fa-4x mb-3"></i><br>No photos available
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <h1 class="display-5 fw-bold mb-2"><?= htmlspecialchars($toy['toy_name']) ?></h1>
            <p class="h5 text-muted mb-4">
                <?= htmlspecialchars($toy['year_released'] ?? '') ?> &bull; <?= htmlspecialchars($toy['line_name'] ?? '') ?>
            </p>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4 fw-bold text-uppercase small text-muted">Condition</div>
                        <div class="col-8"><?= htmlspecialchars($toy['condition'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold text-uppercase small text-muted">Completeness</div>
                        <div class="col-8">
                            <?php if(($toy['completeness_grade'] ?? '') === 'Incomplete'): ?>
                                <span class="text-danger">Incomplete</span>
                            <?php else: ?>
                                <span class="text-success"><?= htmlspecialchars($toy['completeness_grade'] ?? 'Unknown') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold text-uppercase small text-muted">Acquired</div>
                        <div class="col-8"><?= htmlspecialchars($toy['purchase_date'] ?? 'Unknown') ?></div>
                    </div>
                </div>
            </div>

            <?php if(!empty($toy['user_comments'])): ?>
                <div class="mb-4">
                    <h5 class="fw-bold border-bottom pb-2">Collector's Notes</h5>
                    <p class="fst-italic text-muted">"<?= nl2br(htmlspecialchars($toy['user_comments'])) ?>"</p>
                </div>
            <?php endif; ?>

            <?php if(!empty($items)): ?>
                <h5 class="fw-bold border-bottom pb-2 mt-5">Contents / Accessories</h5>
                <ul class="list-group list-group-flush">
                    <?php foreach($items as $item): ?>
                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-circle fa-xs me-2 text-muted"></i>
                                <?= htmlspecialchars($item['master_toy_item_name']) ?>
                                <?php if(!empty($item['variant_description'])): ?>
                                    <small class="text-muted d-block ms-4"><?= htmlspecialchars($item['variant_description']) ?></small>
                                <?php endif; ?>
                            </div>
                            <?php if(!empty($item['condition'])): ?>
                                <span class="badge bg-light text-dark border"><?= $item['condition'] ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-5 pt-5 border-top">
        <h3 class="fw-bold mb-4">More from the Collection</h3>
        
        <?php if(!empty($related['variants'])): ?>
            <p class="text-muted small text-uppercase fw-bold mb-2">Variants of this toy</p>
            <div class="d-flex gap-3 overflow-auto pb-3 mb-4" style="scrollbar-width: thin;">
                <?php foreach($related['variants'] as $rel): ?>
                    <div class="card border-0 shadow-sm" style="min-width: 200px; width: 200px;">
                        <a href="/?module=Collection&controller=Showcase&action=view&id=<?= $rel['id'] ?>" class="text-decoration-none text-dark">
                            <img src="<?= $rel['image_path'] ?? '/assets/img/placeholder.png' ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2 text-center">
                                <small class="fw-bold d-block text-truncate"><?= htmlspecialchars($rel['name']) ?></small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($related['line_mates'])): ?>
            <p class="text-muted small text-uppercase fw-bold mb-2">Same Toy Line</p>
            <div class="d-flex gap-3 overflow-auto pb-3" style="scrollbar-width: thin;">
                <?php foreach($related['line_mates'] as $rel): ?>
                    <div class="card border-0 shadow-sm" style="min-width: 200px; width: 200px;">
                        <a href="/?module=Collection&controller=Showcase&action=view&id=<?= $rel['id'] ?>" class="text-decoration-none text-dark">
                            <img src="<?= $rel['image_path'] ?? '/assets/img/placeholder.png' ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2 text-center">
                                <small class="fw-bold d-block text-truncate"><?= htmlspecialchars($rel['name']) ?></small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>