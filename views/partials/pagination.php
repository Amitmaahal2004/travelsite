<?php
/**
 * Reusable Pagination Partial
 * Expects $meta = ['total', 'per_page', 'current_page', 'total_pages'] and optional $baseUrl
 */
if (!empty($meta) && ($meta['total_pages'] ?? 1) > 1):
    $query = $_GET;
    $current = (int) $meta['current_page'];
    $totalPages = (int) $meta['total_pages'];
    $baseUrl = $baseUrl ?? current_route_path();
?>
<div class="pagination-bar">
    <div style="font-size:0.84rem;color:var(--ink-soft);">
        Showing page <strong><?= $current ?></strong> of <strong><?= $totalPages ?></strong> (<?= (int) $meta['total'] ?> total records)
    </div>
    <div class="pagination-links">
        <?php if ($current > 1):
            $query['page'] = $current - 1; ?>
            <a class="page-chip" href="<?= url($baseUrl) . '?' . http_build_query($query) ?>">&larr; Prev</a>
        <?php endif; ?>

        <?php for ($p = max(1, $current - 2); $p <= min($totalPages, $current + 2); $p++):
            $query['page'] = $p; ?>
            <a class="page-chip <?= $p === $current ? 'active' : '' ?>" href="<?= url($baseUrl) . '?' . http_build_query($query) ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($current < $totalPages):
            $query['page'] = $current + 1; ?>
            <a class="page-chip" href="<?= url($baseUrl) . '?' . http_build_query($query) ?>">Next &rarr;</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
