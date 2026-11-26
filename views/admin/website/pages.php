<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Website Pages &amp; Legal Copy</h2>
        <div style="display:flex;flex-direction:column;gap:16px;">
            <?php foreach ($pages as $p): ?>
                <form method="POST" action="<?= url('/admin/pages/' . (int) $p['id'] . '/update') ?>"
                      style="padding:16px;border-radius:12px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <?= \Core\Csrf::field() ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <strong>/<?= e($p['is_system'] ? $p['slug'] : 'page/' . $p['slug']) ?></strong>
                        <label style="font-size:0.84rem;"><input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>> Published</label>
                    </div>
                    <div class="form-grid-2" style="margin-bottom:10px;">
                        <input type="text" name="title" required class="dash-input" value="<?= e($p['title']) ?>">
                        <input type="text" name="subtitle" class="dash-input" value="<?= e($p['subtitle']) ?>" placeholder="Subtitle">
                    </div>
                    <textarea name="content" rows="4" required class="dash-textarea" style="margin-bottom:10px;"><?= e($p['content']) ?></textarea>
                    <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Save Page</button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel-card">
        <h3>Create Custom Page</h3>
        <form method="POST" action="<?= url('/admin/pages/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Page Title *</label><input type="text" name="title" required class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Slug (Optional)</label><input type="text" name="slug" class="dash-input" placeholder="sustainability-charter"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Subtitle</label><input type="text" name="subtitle" class="dash-input"></div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">HTML Content *</label><textarea name="content" rows="5" required class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Publish Page</button>
        </form>
    </div>
</div>
