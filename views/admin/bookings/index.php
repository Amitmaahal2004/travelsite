<div class="panel-card">
    <div class="panel-head">
        <h2>Bookings Registry</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (can('bookings.export')): ?>
                <a href="<?= url('/admin/bookings/export') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Export CSV</a>
            <?php endif; ?>
            <?php if (can('bookings.create')): ?>
                <a href="<?= url('/admin/bookings/create') ?>" class="btn-brand btn-brand-primary btn-sm-pill">+ Manual Booking</a>
            <?php endif; ?>
        </div>
    </div>

    <form method="GET" action="<?= url('/admin/bookings') ?>" class="filter-bar">
        <input type="text" name="q" value="<?= e($filters['q']) ?>" class="dash-input" placeholder="Reference, client, package...">
        <select name="status" class="dash-select">
            <option value="">All Booking Statuses</option>
            <?php foreach ($statuses as $st): ?>
                <option value="<?= e($st) ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="payment_status" class="dash-select">
            <option value="">All Payment States</option>
            <?php foreach (['unpaid','partial','paid','refunded'] as $ps): ?>
                <option value="<?= $ps ?>" <?= $filters['payment_status'] === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Customer</th><th>Package</th><th>Departure</th><th>Specialist</th><th>Total / Paid</th><th>Status</th><th>Manage</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><strong><?= e($b['booking_reference']) ?></strong></td>
                        <td><?= e($b['customer_name']) ?><br><small style="color:var(--dash-muted);"><?= e($b['customer_email']) ?></small></td>
                        <td><?= e($b['package_title'] ?: ($b['custom_title'] ?: 'Custom')) ?></td>
                        <td><?= e($b['travel_date']) ?></td>
                        <td><?= e($b['employee_name'] ?: 'Unassigned') ?></td>
                        <td><strong><?= e(money($b['total_amount'])) ?></strong><br><small style="color:var(--status-success);">Paid: <?= e(money($b['paid_amount'])) ?></small></td>
                        <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                        <td><a href="<?= url('/admin/bookings/' . (int) $b['id']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Manage</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php $baseUrl = '/admin/bookings'; require BASE_PATH . '/views/partials/pagination.php'; ?>
</div>
