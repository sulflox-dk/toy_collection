<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
    <?php foreach($data as $toy): ?>
    <div class="col">
        <a href="?module=Collection&controller=Showcase&action=view&id=<?= $toy['id'] ?>" class="text-decoration-none text-dark">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="transition: transform 0.2s;">
                
                <div class="position-relative bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height: 300px; border-radius: 8px 8px 0 0;">
                    <?php if(!empty($toy['image_path'])): ?>
                        <img src="<?= htmlspecialchars($toy['image_path']) ?>" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;"
                             class="card-img-top"
                             onmouseover="this.style.transform='scale(1.05)'" 
                             onmouseout="this.style.transform='scale(1)'">
                    <?php else: ?>
                        <div class="text-muted opacity-25">
                            <i class="fas fa-image fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($toy['completeness_grade'])): ?>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-dark opacity-75">
                            <?= htmlspecialchars($toy['completeness_grade']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-body text-center p-4">
                    <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($toy['toy_name']) ?></h5>
                    <div class="text-muted small mb-3">
                        <?= htmlspecialchars($toy['release_year'] ?? 'Unknown Year') ?> &bull; <?= htmlspecialchars($toy['line_name']) ?>
                    </div>
                    
                    <?php if(!empty($toy['missing_items_list'])): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Missing Parts</span>
                    <?php else: ?>
                        <span class="badge bg-light text-success border"><i class="fas fa-check me-1"></i> Complete</span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($pages > 1): ?>
<div class="mt-5 d-flex justify-content-center">
    <nav>
        <ul class="pagination">
            <?php for($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <button class="page-link shadow-none" onclick="ShowcaseMgr.loadPage(<?= $i ?>)"><?= $i ?></button>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>

<style>
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>