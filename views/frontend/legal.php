<section class="page-hero">
    <div class="container-editorial">
        <span class="section-eyebrow"><?= e(brand('company_name')) ?></span>
        <h1><?= e($page['title']) ?></h1>
        <?php if (!empty($page['subtitle'])): ?>
            <p style="max-width:640px;opacity:0.86;"><?= e($page['subtitle']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial" style="max-width:880px;">
        <div class="surface-card" style="line-height:1.85;color:var(--ink-muted);">
            <?= $page['content'] ?>
        </div>
    </div>
</section>
