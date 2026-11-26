<section class="page-hero">
    <img src="<?= e(upload_url($post['featured_image'] ?: 'destinations/hero-fjord.jpg')) ?>" alt="<?= e($post['title']) ?>" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow"><?= e($post['category']) ?> &bull; <?= e($post['read_time']) ?></span>
        <h1 style="max-width:860px;"><?= e($post['title']) ?></h1>
        <p style="opacity:0.8;">By <?= e($post['author_name'] ?: brand('company_name')) ?> &bull; <?= e(substr((string) ($post['published_at'] ?? $post['created_at']), 0, 10)) ?></p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial" style="max-width:840px;">
        <div class="surface-card">
            <?php if (!empty($post['featured_image'])): ?>
                <img src="<?= e(upload_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" style="width:100%;height:420px;object-fit:cover;border-radius:12px;margin-bottom:24px;">
            <?php endif; ?>
            <div style="font-size:1.06rem;line-height:1.85;color:var(--ink);">
                <?= $post['content'] ?>
            </div>
            <div style="margin-top:32px;padding-top:20px;border-top:1px solid var(--border-hairline);display:flex;justify-content:space-between;align-items:center;">
                <a href="<?= url('/blog') ?>" class="btn-brand btn-brand-outline">&larr; Back to Journal</a>
                <a href="<?= url('/custom-enquiry') ?>" class="btn-brand btn-brand-primary">Plan a Similar Journey</a>
            </div>
        </div>
    </div>
</section>
