<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Departments</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Department</th><th>Manager</th><th>Staff</th></tr></thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                        <tr>
                            <td><strong><?= e($d['code']) ?></strong></td>
                            <td><strong><?= e($d['name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($d['description']) ?></small></td>
                            <td><?= e($d['manager_name'] ?: 'Unassigned') ?></td>
                            <td><span class="badge-pill badge-info"><?= (int) $d['headcount'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Create Department</h3>
        <form method="POST" action="<?= url($prefix . '/departments/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Department Name *</label><input type="text" name="name" required class="dash-input"></div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Code *</label><input type="text" name="code" required class="dash-input" placeholder="e.g. FIN"></div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Department Lead</label>
                <select name="manager_user_id" class="dash-select">
                    <option value="">Select Manager</option>
                    <?php foreach ($staffUsers as $su): ?>
                        <option value="<?= (int) $su['id'] ?>"><?= e($su['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Description</label><textarea name="description" rows="3" class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create Department</button>
        </form>
    </div>
</div>
