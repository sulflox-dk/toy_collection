<div class="container-fluid px-4">
    <h1 class="mt-4">Import Data</h1>
    
    <div class="row mb-4">
        <?php foreach ($stats as $stat): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5><?= htmlspecialchars($stat['name']) ?></h5>
                    <div class="display-6"><?= $stat['imported_count'] ?></div>
                    <div class="small">Imported Toys</div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white text-decoration-none" href="#">
                        <i class="fas fa-list-ul me-1"></i> View Logs
                    </a>
                    
                    <?php 
                        // Sikr at URL har http foran, ellers virker linket ikke
                        $url = $stat['base_url'];
                        if (strpos($url, 'http') === false) {
                            $url = 'https://' . $url;
                        }
                    ?>
                    <a class="small text-white text-decoration-none" href="<?= $url ?>" target="_blank" title="Go to site">
                        Visit Site <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cloud-download-alt me-1"></i>
            Run Import
        </div>
        <div class="card-body">
            <form id="importForm" onsubmit="return false;">
                <div class="input-group input-group-lg">
                    <input type="text" id="importUrl" class="form-control" placeholder="Paste URL here (e.g. https://galacticfigures.com/figureDetails.aspx?id=...)" required>
                    <button class="btn btn-primary" type="button" id="btnPreview">
                        <i class="fas fa-search me-2"></i> Analyze URL
                    </button>
                </div>
                <div class="form-text">Supports overview pages (imports first 5) and single detail pages.</div>
            </form>
        </div>
    </div>

    <div id="importResults" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Found Items <span id="itemCount" class="badge bg-secondary">0</span></h3>
            <button id="btnRunImport" class="btn btn-success btn-lg">
                <i class="fas fa-file-import me-2"></i> Import Selected
            </button>
        </div>
        
        <div id="resultsGrid" class="row">
            </div>
    </div>
</div>