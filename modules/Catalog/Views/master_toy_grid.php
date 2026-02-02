<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th style="width: 60px;" class="ps-3">Img</th>
                <th>Name / Details</th>
                <th>Source</th>
                <th>Line / Manufacturer</th>
                <th class="text-center">Items</th>
                <th class="text-center">Owned</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $r): ?>
                <tr data-id="<?= $r['id'] ?>">
                    <td class="ps-3">
                        <div style="width: 40px; height: 40px;" class="bg-light border rounded d-flex align-items-center justify-content-center overflow-hidden">
                            <?php if(!empty($r['image_path'])): ?>
                                <img src="<?= htmlspecialchars($r['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-robot text-muted opacity-50"></i>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($r['name']) ?></div>
                        <div class="small text-muted">
                            <?php 
                                $meta = [];
                                if($r['release_year']) $meta[] = $r['release_year'];
                                if($r['wave_number']) $meta[] = 'Wave ' . $r['wave_number'];
                                if($r['product_type']) $meta[] = $r['product_type'];
                                echo implode(' &bull; ', array_map('htmlspecialchars', $meta));
                            ?>
                        </div>
                    </td>
                    <td>
                        <?php if(!empty($r['source_name'])): ?>
                            <div class="fw-medium"><?= htmlspecialchars($r['source_name']) ?></div>
                            <div class="small text-muted">
                                <?= htmlspecialchars($r['source_type'] ?? '') ?>
                                <?php if(!empty($r['source_year'])) echo ' / ' . $r['source_year']; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <div class="fw-medium"><?= htmlspecialchars($r['line_name'] ?? '-') ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($r['manufacturer_name'] ?? '-') ?></div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            <?= $r['item_count'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if($r['collection_count'] > 0): ?>
                            <span class="badge bg-light text-dark border" title="In your collection">
                                <?= $r['collection_count'] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">0</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-3">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary btn-edit" title="Edit Data">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-media" title="Manage Photos">
                                <i class="fas fa-camera"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if(empty($data)): ?>
                <tr><td colspan="5" class="text-center py-5 text-muted">No toys found in catalog.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="p-3 border-top d-flex justify-content-center bg-light">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="MasterToyMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                <?php for($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="MasterToyMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="MasterToyMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>