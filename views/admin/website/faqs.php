<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Frequently Asked Questions</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Category</th><th>Question</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($faqs as $f): ?>
                        <tr>
                            <td><span class="badge-pill badge-neutral"><?= e($f['category']) ?></span></td>
                            <td><strong><?= e($f['question']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($f['answer']) ?></small></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/faqs/' . (int) $f['id'] . '/delete') ?>" style="margin:0;">
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
        <h3>Add FAQ</h3>
        <form method="POST" action="<?= url('/admin/faqs/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Category *</label><input type="text" name="category" required class="dash-input" value="Booking & Planning"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Question *</label><input type="text" name="question" required class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Answer *</label><textarea name="answer" rows="4" required class="dash-textarea"></textarea></div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">Sort Order</label><input type="number" name="sort_order" class="dash-input" value="10"></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Add FAQ</button>
        </form>
    </div>
</div>
