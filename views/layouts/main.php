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
    
    <link href="<?= Config::get('base_url') ?>assets/css/style.css" rel="stylesheet">
    
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
            <li class="nav-item">
              <a class="nav-link" href="<?= Config::get('base_url') ?>?module=catalog&action=index">Catalog</a>
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
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"> 
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