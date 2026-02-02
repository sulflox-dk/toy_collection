<?php if (empty($images)): ?>
    <div class="p-5 text-center text-muted w-100">
        <i class="far fa-images fa-3x mb-3"></i>
        <p>No media files found matching your criteria.</p>
    </div>
<?php else: ?>
    
    <div class="media-grid">
        <?php foreach ($images as $img): ?>
            <div class="media-item" data-id="<?= $img['id'] ?>" onclick="MediaLib.onItemClick(this, event)">
                <input type="checkbox" class="media-select-checkbox form-check-input" value="<?= $img['id'] ?>">
                <img src="<?= $img['file_path'] ?>" loading="lazy" alt="Media <?= $img['id'] ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <?php 
            $cur = (int)($_GET['page'] ?? 1); 
        ?>
        <div class="pagination-container">
            <nav>
                <ul class="pagination pagination-sm">
                    <li class="page-item <?= $cur <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" onclick="MediaLib.loadPage(<?= $cur - 1 ?>); return false;">&laquo;</a>
                    </li>
                    
                    <?php for($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= $i == $cur ? 'active' : '' ?>">
                            <a class="page-link" href="#" onclick="MediaLib.loadPage(<?= $i ?>); return false;"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $cur >= $pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" onclick="MediaLib.loadPage(<?= $cur + 1 ?>); return false;">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>

<?php endif; ?>