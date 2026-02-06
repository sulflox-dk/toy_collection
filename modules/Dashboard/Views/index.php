<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-dark">Overview</h1>
    <button class="btn btn-dark btn-sm" onclick="CollectionForm.openAddModal()">
        <i class="fas fa-plus me-1"></i> Add New
    </button>
</div>

<?php foreach ($stats as $universeName => $manufacturers): ?>
    <div class="card universe-card">
        <div class="card-header universe-card-header">
            <h5 class="mb-0"><i class="fa-solid fa-globe me-2"></i> <?= htmlspecialchars($universeName) ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="col-man" rowspan="2">Manufacturer</th>
                            <th class="col-line" rowspan="2">Line</th>
                            
                            <th colspan="2" class="text-center border-start">Figures</th>
                            <th colspan="2" class="text-center border-start">Others</th>
                            
                            <th class="text-center border-start col-action" rowspan="2">&nbsp;</th>
                        </tr>
                        <tr>
                            <th class="text-center border-start bg-light col-stat"><small class="text-muted">Owned</small></th>
                            <th class="text-center bg-light col-stat"><small class="text-muted">Pending</small></th>
                            
                            <th class="text-center border-start bg-light col-stat"><small class="text-muted">Owned</small></th>
                            <th class="text-center bg-light col-stat"><small class="text-muted">Pending</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($manufacturers as $manName => $lines): ?>
                            <?php foreach ($lines as $index => $line): ?>
                                <tr>
                                    <td>
                                        <?= ($index === 0) ? htmlspecialchars($manName) : '' ?>
                                    </td>
                                    <td><?= htmlspecialchars($line['line']) ?></td>
                                    
                                    <td class="text-center border-start">
                                        <?php if($line['fig_owned'] > 0): ?>
                                            <a href="?module=collection&line=<?= $line['line_id'] ?>&type=fig" class="text-dark text-decoration-none">
                                                <?= $line['fig_owned'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if($line['fig_pending'] > 0): ?>
                                            (<?= $line['fig_pending'] ?>)
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center border-start">
                                        <?php if($line['other_owned'] > 0): ?>
                                            <a href="?module=collection&line=<?= $line['line_id'] ?>&type=other" class="text-dark text-decoration-none">
                                                <?= $line['other_owned'] ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if($line['other_pending'] > 0): ?>
                                            (<?= $line['other_pending'] ?>)
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center border-start">
                                        <a href="?module=collection&line=<?= $line['line_id'] ?>" class="text-dark" title="View List">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="card recent-card">
    <div class="card-header recent-card-header">
        <h5 class="mb-0 text-gray-800">
            <i class="fas fa-clock me-2"></i>Recently Added
        </h5>
    </div>
    <div class="card-body p-0">
        <?php 
        // Vi genbruger grid-viewet fra Collection modulet!
        // Vi sender 'hide_pagination' => true med, så vi slipper for sidetal
        $this->renderPartial('grid', $data['recentToys'], 'Collection'); 
        ?>
    </div>
</div>