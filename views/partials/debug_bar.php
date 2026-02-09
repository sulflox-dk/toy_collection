<?php
use CollectionApp\Kernel\Config;
?>

<link rel="stylesheet" href="<?= Config::get('base_url') ?>assets/css/core/debug.css">

<div id="debug-bar">
    <div id="debug-header" onclick="document.getElementById('debug-content').style.display = (document.getElementById('debug-content').style.display === 'block' ? 'none' : 'block')">
        <strong>DEBUG</strong>
        <span>Load: <?= $totalTime ?>ms</span>
        <span>SQL: <?= $queryCount ?> (<?= $sqlTime ?>ms)</span>
        
        <?php if($errorCount > 0): ?>
            <span class="status-err">Logs: <?= $errorCount ?></span>
        <?php else: ?>
            <span class="status-ok">Logs: OK</span>
        <?php endif; ?>
    </div>
    
    <div id="debug-content">
        <div class="debug-section">
            <h3>SQL Queries</h3>
            <?php if(empty($queries)): ?>
                <p class="text-muted">No queries.</p>
            <?php else: ?>
                <?php foreach($queries as $q): ?>
                    <div class="dbg-sql">
                        <span class="dbg-time"><?= round($q['duration'] * 1000, 2) ?> ms</span>
                        <code><?= htmlspecialchars($q['sql']) ?></code>
                        <?php if(!empty($q['params'])): ?>
                            <small class="text-muted"><?= json_encode($q['params']) ?></small>
                        <?php endif; ?>
                        <?php if($q['error']): ?><br><span class="dbg-err">Error: <?= $q['error'] ?></span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="debug-section">
            <h3>Logs</h3>
            <?php if(empty($logs)): ?>
                <p class="text-muted">No logs.</p>
            <?php else: ?>
                <?php foreach($logs as $l): ?>
                    <div>
                        <span class="text-muted">[<?= round($l['time']*1000,0) ?>ms]</span> 
                        <strong><?= $l['type'] ?></strong>: <?= htmlspecialchars($l['message']) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>