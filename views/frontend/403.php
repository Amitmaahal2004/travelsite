<section class="page-hero">
    <div class="container-editorial">
        <span class="section-eyebrow">403 &bull; Access Restricted</span>
        <h1>Permission Required</h1>
        <p style="opacity:0.85;">Your account does not hold permission to access this module<?= !empty($requiredPermission) ? ' (' . e($requiredPermission) . ')' : '' ?>.</p>
        <div style="margin-top:24px;display:flex;gap:12px;">
            <a href="<?= url('/') ?>" class="btn-brand btn-brand-outline-light">Public Website</a>
            <?php if (\Core\Auth::check()): ?>
                <a href="<?= e(\Core\Auth::dashboardUrl()) ?>" class="btn-brand btn-brand-accent">My Dashboard</a>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="btn-brand btn-brand-accent">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</section>
