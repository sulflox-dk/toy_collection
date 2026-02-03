<?php
use CollectionApp\Kernel\Config;
// $title and $content come from Template::render()
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? $title . ' | ' : '' ?>Toy Collection Tracker</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="<?= Config::get('base_url') ?>assets/css/core.css?v=<?= time() ?>" rel="stylesheet">
    <link href="<?= Config::get('base_url') ?>assets/css/dashboard.css?v=<?= time() ?>" rel="stylesheet">
    <link href="<?= Config::get('base_url') ?>assets/css/collection.css?v=<?= time() ?>" rel="stylesheet">
    <link href="<?= Config::get('base_url') ?>assets/css/catalog.css?v=<?= time() ?>" rel="stylesheet">
    <link href="<?= Config::get('base_url') ?>assets/css/media_library.css?v=<?= time() ?>" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  </head>
  <body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <a class="navbar-brand" href="<?= Config::get('base_url') ?>">
            <i class="fa-solid fa-jedi"></i> ToyTracker
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="<?= Config::get('base_url') ?>">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= Config::get('base_url') ?>?module=collection&action=index">My Collection</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Catalog</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Catalog&controller=MasterToy&action=index">Master Toys</a></li>
                  <li><hr class="dropdown-divider"></li>  
                  <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Catalog&controller=ProductType&action=index">Product Types</a></li>  
                  <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Catalog&controller=ToyLine&action=index">Toy Lines</a></li>   
                  <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Catalog&controller=Manufacturer&action=index">Manufacturers</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    Data
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Universe&controller=Universe&action=index">Universes</a></li>
                    <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Universe&controller=Subject&action=index">Subjects</a></li>
                    <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=Universe&controller=EntertainmentSource&action=index">Entertainment Sources</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    Media
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=media&controller=MediaLibrary&action=index">Media Library</a></li>
                    <li><a class="dropdown-item" href="<?= Config::get('base_url') ?>?module=media&controller=MediaTags&action=index">Media Tags</a></li>
                </ul>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
             <li class="nav-item">
                 <a class="nav-link" href="#"><i class="fa-solid fa-gear"></i> Settings</a>
             </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container min-vh-100">
        <?= $content ?>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <small>&copy; <?= date('Y') ?> Toy Collection Manager. May the Force be with you.</small>
        </div>
    </footer>

    <div class="modal fade" id="appModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
      </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3 mt-5" style="z-index: 2000">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastBody">
                    Action successful!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    </body>

    <script>
        const SITE_URL = "<?= Config::get('base_url') ?>";
    </script>

    <script src="<?= Config::get('base_url') ?>assets/js/app.js"></script>
    
    <?php if (isset($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= Config::get('base_url') . $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>