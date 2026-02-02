<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-3">Name</th>
                <th>Universe</th>
                <th style="width: 15%">Type</th>
                <th style="width: 10%">Year</th>
                <th class="text-center">Toys</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $s): ?>
                <tr data-json='<?= json_encode($s, JSON_HEX_APOS) ?>'>
                    <td class="ps-3 fw-medium"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($s['universe_name']) ?></td>
                    <td class="small">
                        <span class="badge bg-light text-dark border"><?= $s['type'] ?></span>
                    </td>
                    <td class="small text-muted">
                        <?= $s['release_year'] ? $s['release_year'] : '-' ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border" title="Master Toys linked">
                            <i class="fas fa-robot me-1"></i> <?= $s['toy_count'] ?>
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
            
            <?php if(empty($data)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-2x mb-3 d-block opacity-25"></i>
                        No sources found matching your filters.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="p-3 border-top d-flex justify-content-center bg-light">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="SourceMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                
                <?php for($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="SourceMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="SourceMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>