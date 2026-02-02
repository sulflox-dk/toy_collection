<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-3">Name</th>
                <th class="text-center">Dashboard</th>
                <th class="text-center">Toy Lines</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $m): ?>
                <tr data-id="<?= $m['id'] ?>" data-json='<?= json_encode($m, JSON_HEX_APOS) ?>'>
                    <td class="ps-3 fw-bold"><?= htmlspecialchars($m['name']) ?></td>
                    <td class="text-center">
                        <?php if($m['show_on_dashboard']): ?>
                            <i class="fas fa-check text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-times text-muted opacity-25"></i>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-list me-1"></i> <?= $m['line_count'] ?>
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
                <tr><td colspan="4" class="text-center py-5 text-muted">No manufacturers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="p-3 border-top d-flex justify-content-center bg-light">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="ManMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                <?php for($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="ManMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="ManMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>