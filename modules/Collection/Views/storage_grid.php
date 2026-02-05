<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-3" style="width: 120px;">Code</th>
                <th>Name</th>
                <th>Location</th>
                <th class="text-center" style="width: 80px;">Toys</th>
                <th class="text-end pe-3" style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr data-json='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <td class="ps-3">
                        <h5 class="mb-0">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-box text-warning me-1"></i><?= htmlspecialchars($row['box_code']) ?>
                            </span>
                        </h5>
                    </td>
                    <td class="fw-medium">
                        <?= htmlspecialchars($row['name']) ?>
                    </td>
                    <td class="text-muted small">
                        <?php if(!empty($row['location_room'])): ?>
                            <i class="fas fa-map-marker-alt me-1 opacity-50"></i><?= htmlspecialchars($row['location_room']) ?>
                        <?php else: ?>
                            <span class="opacity-25">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border" title="Toys in this box">
                            <?= $row['toy_count'] ?>
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
            
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-box-open mb-2 fs-4 opacity-25"></i><br>
                        No storage units found.
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
                    <a class="page-link" href="#" onclick="StorageMgr.loadPage(<?= $current_page - 1 ?>); return false;">&laquo;</a>
                </li>
                <?php for($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="StorageMgr.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" onclick="StorageMgr.loadPage(<?= $current_page + 1 ?>); return false;">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>