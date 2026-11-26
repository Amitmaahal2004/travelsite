<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Travel Categories</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Category</th><th>Icon</th><th>Packages</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><strong><?= e($c['name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($c['tagline']) ?></small></td>
                            <td><?= e($c['icon']) ?></td>
                            <td><span class="badge-pill badge-info"><?= (int) $c['package_count'] ?></span></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/categories/' . (int) $c['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" data-confirm="Remove category?" class="btn-brand btn-brand-outline btn-sm-pill">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Add Category</h3>
        <form method="POST" action="<?= url('/admin/categories/create') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Name *</label><input type="text" name="name" required class="dash-input"></div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Tagline</label><input type="text" name="tagline" class="dash-input"></div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Icon</label>
                <select name="icon" class="dash-select">
                    <?php foreach (['compass','mountain','users','heart','landmark','binoculars','gem','sun'] as $ic): ?>
                        <option value="<?= $ic ?>"><?= ucfirst($ic) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Description</label><textarea name="description" rows="2" class="dash-textarea"></textarea></div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Cover Image</label><input type="file" name="image" accept="image/*" class="dash-input"></div>
            <label style="display:block;margin-bottom:14px;"><input type="checkbox" name="is_featured" value="1" checked> Featured</label>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create Category</button>
        </form>
    </div>
</div>
