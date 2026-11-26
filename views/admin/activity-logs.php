<div class="panel-card">
    <div class="panel-head">
        <h2>Audit &amp; Activity Logs</h2>
        <form method="GET" action="<?= url('/admin/activity-logs') ?>" class="filter-bar" style="margin:0;">
            <select name="module" class="dash-select" onchange="this.form.submit()">
                <option value="">All Modules</option>
                <?php foreach ($modules as $m): ?>
                    <option value="<?= e($m['module']) ?>" <?= $activeModule === $m['module'] ? 'selected' : '' ?>><?= e($m['module']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= e($l['created_at']) ?></td>
                        <td><?= e($l['user_name'] ?: 'System') ?></td>
                        <td><span class="badge-pill badge-neutral"><?= e($l['module']) ?></span></td>
                        <td><strong><?= e($l['action']) ?></strong></td>
                        <td><?= e($l['description']) ?></td>
                        <td><?= e($l['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php $baseUrl = '/admin/activity-logs'; require BASE_PATH . '/views/partials/pagination.php'; ?>
</div>
