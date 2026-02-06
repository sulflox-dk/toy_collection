<?php
$getStatusColor = function($status) {
    return match($status) {
        'In Hand', 'Arrived' => 'success',
        'Pre-order' => 'info',
        'Shipped' => 'primary',
        'Paid' => 'primary',
        'Backordered' => 'warning',
        'Customs' => 'warning',
        'Cancelled' => 'danger',
        default => 'secondary'
    };
};
$mode = $view_mode ?? 'list';
?>

<?php if ($mode === 'cards'): ?>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 mb-4 p-3">
        <?php foreach($data as $r): ?>
            <div class="col">
                <div class="card h-100 toy-card shadow-none" data-id="<?= $r['id'] ?>" style="border:0px solid rgb(222, 226, 230);">
                    <div class="position-relative bg-light border-bottom d-flex align-items-center justify-content-center p-0" style="height: 260px; border:1px solid rgb(222, 226, 230);">
                        <?php if(!empty($r['image_path'])): ?>
                            <img src="<?= htmlspecialchars($r['image_path']) ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;" loading="lazy">
                        <?php else: ?>
                            <div class="text-center text-muted opacity-25"><i class="fas fa-camera fa-4x mb-2"></i><br>No Image</div>
                        <?php endif; ?>
                        <?php if($r['acquisition_status'] != 'Arrived'): ?>
                            <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-warning shadow-sm"><?= $r['acquisition_status'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column" style="border:1px solid rgb(222, 226, 230); border-top-width:0px; border-bottom-width:0px;">
                        <div class="mb-2">
                            <h6 class="card-title text-dark fw-bold text-truncate" title="<?= htmlspecialchars($r['toy_name']) ?>"><?= htmlspecialchars($r['toy_name']) ?></h6>
                            <div class="small text-secondary">
                                <?php 
                                    $meta = [];
                                    if($r['release_year']) $meta[] = $r['release_year'];
                                    if($r['product_type']) $meta[] = $r['product_type'];
                                    echo implode(' &bull; ', array_map('htmlspecialchars', $meta));
                                ?>
                            </div>
                            <?php if(!empty($r['source_name'])): ?>
                                <div class="small text-secondary"><?= htmlspecialchars($r['source_name']) ?>
                                    <?= ' &bull; ' . htmlspecialchars($r['source_year'] ?? '') ?>
                                    <?php if(!empty($r['source_type'])) echo ' &bull; ' . htmlspecialchars($r['source_type']); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-secondary small">-</span>
                            <?php endif; ?>
                            <div class="text-secondary small"><?= htmlspecialchars($r['manufacturer_name'] ?? '-') ?> &bull; <?= htmlspecialchars($r['line_name'] ?? '') ?></div>
                        </div>
                        <div class="row border-top pt-2">
                            <div class="col-6 small">
                                <div>Condition: <?= htmlspecialchars($r['condition'] ?? '-') ?></div>
                                <div>
                                <?php if(!empty($r['box_code'])): ?>
                                    <span class="small">Storage: <?= htmlspecialchars($r['box_code']) ?></span>
                                <?php else: ?>
                                    <span class="small">Storage: ?</span>
                                <?php endif; ?>
                                <span class="small"> &bull; </span>
                                <?php if(!empty($r['personal_toy_id'])): ?>
                                    <span class="small">
                                        ID: <?= htmlspecialchars($r['personal_toy_id']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="small">ID: ?</span>
                                <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-6 text-end"><?php if(!empty($r['item_count']) && $r['item_count'] > 0): ?><span class="badge bg-light text-dark border"><?= $r['item_count'] ?> Parts</span><?php endif; ?></div>                                                       
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 p-2" style="border:1px solid rgb(222, 226, 230); border-top-width:0px;">
                        <div class="btn-group w-100 shadow-sm">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-edit"><i class="fas fa-pencil-alt"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-media"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($data)): ?>
            <div class="col-12 text-center py-5 text-muted">No items found in your collection.</div>
        <?php endif; ?>
    </div>

<?php else: ?>

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
                                <span class="position-absolute top-0 end-0 p-1 m-1 bg-warning border border-light rounded-circle" style="transform: translate(30%, -30%);"></span>
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
<?php endif; ?>

<?php if (isset($pages) && $pages > 1 && !isset($hide_pagination)): ?>
    <div class="p-3 border-top d-flex justify-content-center bg-light mt-auto">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="CollectionMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                <?php 
                // Begræns antallet af viste sider (valgfrit, men pænt)
                $start = max(1, $current_page - 2);
                $end = min($pages, $current_page + 2);
                
                if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                
                for($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="CollectionMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; 
                
                if($end < $pages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                ?>
                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="CollectionMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>