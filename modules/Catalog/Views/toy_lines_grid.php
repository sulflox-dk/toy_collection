<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-3">Name / Details</th>
                <th class="text-center">Dashboard</th>
                <th>Manufacturer</th>
                <th>Universe</th>
                <th class="text-center">Toys</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $r): ?>
                <tr data-id="<?= $r['id'] ?>" data-json='<?= json_encode($r, JSON_HEX_APOS) ?>'>
                    <td class="ps-3">
                        <span class="fw-bold"><?= htmlspecialchars($r['name']) ?></span>
                        <?php 
                            // Byg info-strengen dynamisk
                            $meta = [];
                            if (!empty($r['scale'])) $meta[] = $r['scale'];
                            if (!empty($r['era_start_year'])) $meta[] = $r['era_start_year'];
                            
                            if (!empty($meta)): 
                        ?>
                            <span class="text-muted fw-normal ms-1">
                                (<?= implode(' / ', array_map('htmlspecialchars', $meta)) ?>)
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <?php if($r['show_on_dashboard']): ?>
                            <i class="fas fa-check text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-times text-muted opacity-25"></i>
                        <?php endif; ?>
                    </td>

                    <td class="small"><?= htmlspecialchars($r['manufacturer_name'] ?? '-') ?></td>
                    <td class="small"><?= htmlspecialchars($r['universe_name'] ?? '-') ?></td>
                    
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-robot me-1"></i> <?= $r['toy_count'] ?>
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary btn-edit"><i class="fas fa-pencil-alt"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-delete"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if(empty($data)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No toy lines found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="p-3 border-top d-flex justify-content-center bg-light">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="LineMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                <?php for($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="LineMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="LineMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>