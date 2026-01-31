<?php use CollectionApp\Kernel\Config; ?>

<div class="modal-header border-0 pb-0">
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body d-flex flex-column align-items-center justify-content-top h-100">
    
    <h3 class="text-center mb-5 fw-light text-uppercase ls-2">Choose Universe</h3>

    <div class="container" style="max-width: 900px;">
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
                        
                        <span style="display:none; font-weight:bold; color: #000;"><?= htmlspecialchars($u['name']) ?></span>
                    
                    </button>
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</div>