<?php
$flashes = all_flashes();
if (!empty($flashes)):
?>
<div class="flash-stack">
    <?php foreach ($flashes as $type => $msg): ?>
        <div class="flash-alert flash-<?= e($type) ?>" role="alert">
            <span><?= e($msg) ?></span>
            <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
