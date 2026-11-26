<div class="dash-grid-2" style="grid-template-columns:1.4fr 0.85fr;">
    <div class="panel-card">
        <h2>Media Library</h2>
        <div class="form-grid-3">
            <?php foreach ($media as $m): ?>
                <div style="border:1px solid var(--dash-border);border-radius:10px;overflow:hidden;background:var(--dash-surface-alt);">
                    <img src="<?= e(upload_url($m['file_path'])) ?>" alt="<?= e($m['alt_text']) ?>" style="height:130px;width:100%;object-fit:cover;">
                    <div style="padding:10px;font-size:0.8rem;">
                        <strong style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($m['title']) ?></strong>
                        <small style="color:var(--dash-muted);"><?= e($m['file_path']) ?></small>
                        <form method="POST" action="<?= url('/admin/media/' . (int) $m['id'] . '/delete') ?>" style="margin-top:8px;">
                            <?= \Core\Csrf::field() ?>
                            <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill" style="width:100%;">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel-card">
        <h3>Upload Image Asset</h3>
        <form method="POST" action="<?= url('/admin/media/upload') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Title</label><input type="text" name="title" class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Alt Text</label><input type="text" name="alt_text" class="dash-input"></div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">Image File *</label><input type="file" name="media_file" required accept="image/*" class="dash-input"></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Upload to Library</button>
        </form>
    </div>
</div>
