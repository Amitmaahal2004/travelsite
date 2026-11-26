<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.95fr;">
    <div class="panel-card">
        <h2>Blog &amp; Travel Stories</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($posts as $p): ?>
                        <tr>
                            <td><strong><?= e($p['title']) ?></strong><br><small style="color:var(--dash-muted);">/blog/<?= e($p['slug']) ?></small></td>
                            <td><?= e($p['category']) ?></td>
                            <td><span class="badge-pill <?= status_badge_class($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span></td>
                            <td style="display:flex;gap:6px;">
                                <a href="<?= url('/blog/' . $p['slug']) ?>" target="_blank" class="btn-brand btn-brand-outline btn-sm-pill">View</a>
                                <form method="POST" action="<?= url('/admin/blogs/' . (int) $p['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Archive</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Publish Travel Story</h3>
        <form method="POST" action="<?= url('/admin/blogs/create') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Headline *</label><input type="text" name="title" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div><label class="form-label-editorial">Category *</label><input type="text" name="category" required class="dash-input" value="Editorial Guide"></div>
                <div><label class="form-label-editorial">Read Time</label><input type="text" name="read_time" class="dash-input" value="5 min read"></div>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Excerpt</label><textarea name="excerpt" rows="2" class="dash-textarea"></textarea></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Body Content *</label><textarea name="content" rows="5" required class="dash-textarea"></textarea></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div><label class="form-label-editorial">Cover Image</label><input type="file" name="featured_image" accept="image/*" class="dash-input"></div>
                <div>
                    <label class="form-label-editorial">Status</label>
                    <select name="status" class="dash-select"><option value="published">Published</option><option value="draft">Draft</option></select>
                </div>
            </div>
            <label style="display:block;margin-bottom:14px;"><input type="checkbox" name="is_featured" value="1"> Feature on Homepage</label>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Publish Article</button>
        </form>
    </div>
</div>
