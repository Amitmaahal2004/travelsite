<div class="panel-card">
    <div class="panel-head">
        <h2>Customers Directory</h2>
        <form method="GET" action="<?= url('/admin/customers') ?>" class="filter-bar" style="margin:0;">
            <input type="text" name="q" value="<?= e($filters['q']) ?>" class="dash-input" placeholder="Name, email, code...">
            <select name="status" class="dash-select">
                <option value="">All Statuses</option>
                <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="blocked" <?= $filters['status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
            <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Filter</button>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Code</th><th>Customer</th><th>Location</th><th>Tier</th><th>Bookings</th><th>Account</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><strong><?= e($c['customer_code']) ?></strong></td>
                        <td><strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($c['email']) ?></small></td>
                        <td><?= e(trim(($c['city'] ?? '') . ', ' . ($c['country'] ?? ''), ', ') ?: '—') ?></td>
                        <td><span class="badge-pill badge-neutral"><?= e(ucfirst($c['loyalty_tier'])) ?></span></td>
                        <td><?= (int) $c['booking_count'] ?></td>
                        <td><span class="badge-pill <?= status_badge_class($c['account_status']) ?>"><?= e(ucfirst($c['account_status'])) ?></span></td>
                        <td><a href="<?= url('/admin/customers/' . (int) $c['id']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Manage</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php $baseUrl = '/admin/customers'; require BASE_PATH . '/views/partials/pagination.php'; ?>
</div>
