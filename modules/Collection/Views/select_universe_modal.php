<?php use CollectionApp\Kernel\Config; ?>

<div class="modal-header border-0 pb-0">
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body d-flex flex-column align-items-center justify-content-top h-100">
    
    <h3 class="universe-title">Choose Universe</h3>

    <div class="container universe-grid-container">
        <div class="row justify-content-center g-4"> 
            
            <?php foreach($universes as $u): ?>
                <?php 
                    $slug = $u['slug'] ?? 'default';
                    $imgUrl = Config::get('base_url') . 'assets/images/universe_' . $slug . '_square_logo.jpg';
                ?>
                
                <div class="col-6 col-md-4 col-lg-3">
                    <button type="button" 
                            class="btn-universe-select"
                            onclick="App.openModal('Collection', 'Toy', 'form', {universe_id: <?= $u['id'] ?>})">
                        
                        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($u['name']) ?>" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        
                        <span class="fw-bold text-dark d-none"><?= htmlspecialchars($u['name']) ?></span>
                    
                    </button>
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</div>