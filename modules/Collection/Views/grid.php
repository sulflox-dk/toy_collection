<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th style="width: 60px;" class="ps-3">Img</th>
                <th>Name / Details</th>
                <th>Source</th>
                <th>Line / Manufacturer</th>
                <th>Cond. / Stor. / ID</th>
                <th class="text-center">Parts</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $r): ?>
                <tr data-id="<?= $r['id'] ?>">
                    <td class="ps-3">
                        <div style="width: 45px; height: 45px;" class="bg-light border rounded d-flex align-items-center justify-content-center overflow-hidden position-relative">
                            <?php if(!empty($r['image_path'])): ?>
                                <img src="<?= htmlspecialchars($r['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-camera text-muted opacity-25"></i>
                            <?php endif; ?>
                            
                            <?php 
                            if($r['acquisition_status'] != 'Arrived') { ?>                            
                            <span class="position-absolute top-0 end-0 p-1 bg-warning border border-light rounded-circle" style="transform: translate(30%, -30%);"></span>
                            <?php } ?>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($r['toy_name']) ?></div>
                        <div class="small text-muted">
                            <?php 
                                $meta = [];
                                if($r['release_year']) $meta[] = $r['release_year'];
                                if($r['product_type']) $meta[] = $r['product_type'];
                                echo implode(' &bull; ', array_map('htmlspecialchars', $meta));
                            ?>
                        </div>
                    </td>
                    <td>
                        <?php if(!empty($r['source_name'])): ?>
                            <div class="fw-medium"><?= htmlspecialchars($r['source_name']) ?></div>
                            <div class="small text-muted">
                                <?= htmlspecialchars($r['source_year'] ?? '') ?>
                                <?php if(!empty($r['source_type'])) echo ' &bull; ' . htmlspecialchars($r['source_type']); ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <div class="fw-medium"><?= htmlspecialchars($r['line_name'] ?? '-') ?></div>
                        <div class="text-muted"><?= htmlspecialchars($r['manufacturer_name'] ?? '-') ?></div>
                    </td>
                    <td>
                        <?php if(!empty($r['condition'])): ?>
                        <div class="fw-medium"><?= htmlspecialchars($r['condition']) ?></div>  
                        <?php else: ?>
                            <div class="fw-medium text-muted">?</div>
                        <?php endif; ?>  
                        <?php if(!empty($r['box_code'])): ?>
                            <span class="small"><?= htmlspecialchars($r['box_code']) ?></span>
                        <?php else: ?>
                            <span class="small">?</span>
                        <?php endif; ?>
                        <span class="small"> / </span>
                        <?php if(!empty($r['personal_toy_id'])): ?>
                            <span class="small">
                                <?= htmlspecialchars($r['personal_toy_id']) ?>
                        </span>
                        <?php else: ?>
                            <span class="small">?</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            <?= $r['item_count'] ?>
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary btn-edit" title="Edit Collection Details">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-media" title="Manage Photos">
                                <i class="fas fa-camera"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete Toy & Images">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if(empty($data)): ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No items found in your collection.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="p-3 border-top d-flex justify-content-center bg-light">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="CollectionMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                <?php for($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="CollectionMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="CollectionMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>