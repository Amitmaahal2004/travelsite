<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>SEO Metadata Per Page</h2>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($seoList as $s): ?>
                <form method="POST" action="<?= url('/admin/seo/save') ?>" style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <?= \Core\Csrf::field() ?>
                    <input type="hidden" name="page_key" value="<?= e($s['page_key']) ?>">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <strong style="text-transform:uppercase;color:var(--brand-accent);font-size:0.8rem;">Page Key: <?= e($s['page_key']) ?></strong>
                    </div>
                    <div class="form-grid-2" style="margin-bottom:8px;">
                        <input type="text" name="meta_title" required class="dash-input" value="<?= e($s['meta_title']) ?>" placeholder="Meta Title">
                        <input type="text" name="robots" class="dash-input" value="<?= e($s['robots']) ?>" placeholder="index, follow">
                    </div>
                    <input type="text" name="meta_keywords" class="dash-input" style="margin-bottom:8px;" value="<?= e($s['meta_keywords']) ?>" placeholder="Keywords">
                    <textarea name="meta_description" rows="2" class="dash-textarea" style="margin-bottom:10px;" placeholder="Meta description"><?= e($s['meta_description']) ?></textarea>
                    <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Save SEO</button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel-card">
        <h3>Add Page SEO Rule</h3>
        <form method="POST" action="<?= url('/admin/seo/save') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Page Key *</label><input type="text" name="page_key" required class="dash-input" placeholder="e.g. blog, contact, about"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Meta Title *</label><input type="text" name="meta_title" required class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Meta Keywords</label><input type="text" name="meta_keywords" class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Robots</label><input type="text" name="robots" class="dash-input" value="index, follow"></div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">Meta Description</label><textarea name="meta_description" rows="3" class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Save Rule</button>
        </form>
    </div>
</div>
