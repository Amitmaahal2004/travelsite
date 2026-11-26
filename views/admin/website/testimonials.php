<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Customer Testimonials</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Guest</th><th>Quote</th><th>Rating</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td><strong><?= e($t['customer_name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($t['customer_location']) ?></small></td>
                            <td style="max-width:300px;font-size:0.84rem;"><?= e($t['quote']) ?></td>
                            <td><?= (int) $t['rating'] ?>/5</td>
                            <td>
                                <form method="POST" action="<?= url('/admin/testimonials/' . (int) $t['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Add Testimonial</h3>
        <form method="POST" action="<?= url('/admin/testimonials/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Guest Name *</label><input type="text" name="customer_name" required class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Location</label><input type="text" name="customer_location" class="dash-input" placeholder="Zurich, Switzerland"></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div><label class="form-label-editorial">Rating (1-5)</label><input type="number" min="1" max="5" name="rating" class="dash-input" value="5"></div>
                <div>
                    <label class="form-label-editorial">Package</label>
                    <select name="package_id" class="dash-select"><option value="">Optional</option><?php foreach ($packages as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['title']) ?></option><?php endforeach; ?></select>
                </div>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Trip Label</label><input type="text" name="trip_taken" class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Quote *</label><textarea name="quote" rows="4" required class="dash-textarea"></textarea></div>
            <label style="display:block;margin-bottom:14px;"><input type="checkbox" name="is_featured" value="1" checked> Feature on Homepage</label>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Add Testimonial</button>
        </form>
    </div>
</div>
