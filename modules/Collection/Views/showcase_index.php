<div class="d-flex justify-content-between align-items-center mb-5 mt-3">
    <div>
        <h1 class="display-5 fw-bold text-dark mb-0">The Collection</h1>
        <p class="text-muted lead">A curated gallery of vintage toys.</p>
    </div>
    </div>

<div class="card shadow-sm border-0 mb-5" style="background-color: #f8f9fa;">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="small text-muted fw-bold mb-1">UNIVERSE</label>
                <select id="filterUniverse" class="form-select border-0 shadow-sm">
                    <option value="">All Universes</option>
                    <?php foreach($universes as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold mb-1">MANUFACTURER</label>
                <select id="filterManufacturer" class="form-select border-0 shadow-sm">
                    <option value="">All Manufacturers</option>
                    <?php foreach($manufacturers as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold mb-1">PRODUCT TYPE</label>
                <select id="filterProductType" class="form-select border-0 shadow-sm">
                    <option value="">All Types</option>
                    <?php foreach($productTypes as $pt): ?>
                        <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold mb-1">SEARCH</label>
                <input type="text" id="searchShowcase" class="form-control border-0 shadow-sm" placeholder="Search name...">
            </div>
        </div>
    </div>
</div>

<div id="showcaseGrid">
    <?php 
        $data = $initialData['data']; 
        $pages = $initialData['pages']; 
        $current_page = $initialData['current_page'];
        include __DIR__ . '/showcase_grid.php'; 
    ?>
</div>

<script>
    const ShowcaseMgr = {
        loadPage: function(page) {
            const uni = document.getElementById('filterUniverse').value;
            const man = document.getElementById('filterManufacturer').value;
            const type = document.getElementById('filterProductType').value;
            const search = document.getElementById('searchShowcase').value;

            const url = `/?module=Collection&controller=Showcase&action=index&ajax_grid=1&page=${page}&universe_id=${uni}&manufacturer_id=${man}&product_type_id=${type}&search=${search}`;
            
            const container = document.getElementById('showcaseGrid');
            container.style.opacity = '0.5';

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                });
        }
    };

    // Attach listeners
    document.querySelectorAll('select').forEach(el => el.addEventListener('change', () => ShowcaseMgr.loadPage(1)));
    document.getElementById('searchShowcase').addEventListener('keyup', () => {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => ShowcaseMgr.loadPage(1), 500);
    });
</script>