<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Assigned Bookings</div>
        <div class="kpi-value"><?= (int) $stats['assigned_bookings'] ?></div>
        <div class="kpi-sub"><?= e($emp['department_name'] ?: 'Operations') ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Active Tasks</div>
        <div class="kpi-value"><?= (int) $stats['open_tasks'] ?></div>
        <div class="kpi-sub">Pending or in progress</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Pending Leave Requests</div>
        <div class="kpi-value"><?= (int) $stats['pending_leaves'] ?></div>
        <div class="kpi-sub">Awaiting HR review</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Total Commissions</div>
        <div class="kpi-value"><?= e(money($stats['commissions_total'])) ?></div>
        <div class="kpi-sub">Earned across bookings</div>
    </div>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <div class="panel-head">
            <h3>Upcoming Assigned Bookings</h3>
            <a href="<?= url('/employee/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">All Bookings</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ref</th><th>Client</th><th>Package</th><th>Travel Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><a href="<?= url('/employee/bookings/' . (int) $b['id']) ?>"><strong><?= e($b['booking_reference']) ?></strong></a></td>
                            <td><?= e($b['customer_name']) ?></td>
                            <td><?= e($b['package_title'] ?: 'Custom') ?></td>
                            <td><?= e($b['travel_date']) ?></td>
                            <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?><tr><td colspan="5" style="color:var(--dash-muted);">No assigned bookings yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h3>Assigned Tasks</h3>
            <a href="<?= url('/employee/assignments') ?>" class="btn-brand btn-brand-outline btn-sm-pill">View All</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($tasks as $t): ?>
                <div style="padding:12px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong><?= e($t['title']) ?></strong>
                        <div style="font-size:0.78rem;color:var(--dash-muted);">Priority: <?= e(ucfirst($t['priority'])) ?> &bull; Due: <?= e($t['due_date'] ?: 'Flexible') ?></div>
                    </div>
                    <span class="badge-pill <?= status_badge_class($t['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $t['status']))) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($tasks)): ?><p style="color:var(--dash-muted);">No tasks assigned.</p><?php endif; ?>
        </div>
    </div>
</div>
