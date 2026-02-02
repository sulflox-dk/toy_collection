<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Media Library</h1>
</div>

<div class="media-toolbar">
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <select id="filterConnection" class="form-select form-select-sm" style="width: auto;">
            <option value="">All Media items</option>
            <option value="none">Unattached (No connections)</option>
            <option value="collection">Connected to Collection</option>
            <option value="catalog">Connected to Catalog</option>
        </select>

        <select id="filterTag" class="form-select form-select-sm" style="width: auto;">
            <option value="">All Tags</option>
            <?php foreach($tags as $tag): ?>
                <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['tag_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="input-group input-group-sm" style="width: 250px;">
            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
            <input type="text" id="mediaSearch" class="form-control" placeholder="Search filename or toy...">
        </div>
    </div>

    <div class="d-flex gap-2">
        <button id="btnBulkSelect" class="btn btn-sm btn-outline-secondary">Bulk Select</button>
        <button id="btnBulkDelete" class="btn btn-sm btn-danger d-none">Delete Selected (<span id="selectedCount">0</span>)</button>
    </div>
</div>

<div id="mediaGridContainer">
    <div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>
</div>