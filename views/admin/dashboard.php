<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value"><?= e(money($kpis['total_revenue'])) ?></div>
        <div class="kpi-sub">This month: <strong><?= e(money($kpis['monthly_revenue'])) ?></strong></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Pending Payments</div>
        <div class="kpi-value"><?= e(money($kpis['pending_payments'])) ?></div>
        <div class="kpi-sub">Across active balances</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Bookings Overview</div>
        <div class="kpi-value"><?= (int) $kpis['total_bookings'] ?></div>
        <div class="kpi-sub"><?= (int) $kpis['confirmed_bookings'] ?> confirmed &bull; <?= (int) $kpis['cancelled_bookings'] ?> cancelled</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">People &amp; Accounts</div>
        <div class="kpi-value"><?= (int) $kpis['total_customers'] ?> <small style="font-size:0.9rem;color:var(--dash-muted);">Clients</small></div>
        <div class="kpi-sub"><?= (int) $kpis['total_employees'] ?> Employees &bull; <?= (int) $kpis['total_hr_users'] ?> HR Users</div>
    </div>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <div class="panel-head">
            <h3>Revenue &amp; Booking Trends (6 Months)</h3>
            <span class="badge-pill badge-info">Live MySQL Data</span>
        </div>
        <?php
        $maxRev = max(1, ...array_column($trends, 'revenue'));
        ?>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;align-items:end;height:210px;padding:18px 8px 6px;border-bottom:1px solid var(--dash-border);">
            <?php foreach ($trends as $t):
                $pct = max(6, (int) round(($t['revenue'] / $maxRev) * 100));
            ?>
                <div style="display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;gap:8px;">
                    <small style="font-size:0.72rem;font-weight:700;"><?= e(money($t['revenue'])) ?></small>
                    <div style="width:100%;max-width:44px;height:<?= $pct ?>%;background:linear-gradient(180deg, var(--brand-accent), var(--brand-primary));border-radius:8px 8px 2px 2px;" title="<?= e($t['label'] . ': ' . money($t['revenue'])) ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;padding-top:8px;text-align:center;font-size:0.75rem;color:var(--dash-muted);">
            <?php foreach ($trends as $t): ?>
                <div>
                    <strong><?= e($t['label']) ?></strong><br>
                    <span><?= (int) $t['bookings'] ?> bkgs</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h3>Booking Status Distribution</h3>
            <span class="badge-pill badge-warning"><?= (int) $kpis['open_enquiries'] ?> Open Enquiries</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php $totalBk = max(1, $kpis['total_bookings']); ?>
            <?php foreach ($statusCounts as $sc):
                $w = round(((int) $sc['cnt'] / $totalBk) * 100);
            ?>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.86rem;margin-bottom:4px;">
                        <span><?= e($sc['booking_status']) ?></span>
                        <strong><?= (int) $sc['cnt'] ?> (<?= $w ?>%)</strong>
                    </div>
                    <div style="height:8px;background:var(--dash-surface-alt);border-radius:999px;overflow:hidden;">
                        <div style="height:100%;width:<?= $w ?>%;background:var(--brand-accent);border-radius:999px;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($statusCounts)): ?>
                <p style="color:var(--dash-muted);">No bookings recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <div class="panel-head">
            <h3>Recent Bookings</h3>
            <?php if (can('bookings.view')): ?>
                <a href="<?= url('/admin/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Manage All</a>
            <?php endif; ?>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td><strong><?= e($b['booking_reference']) ?></strong></td>
                            <td><?= e($b['customer_name']) ?></td>
                            <td><?= e($b['package_title'] ?: ($b['custom_title'] ?: 'Custom')) ?></td>
                            <td><?= e(money($b['total_amount'], $b['currency'])) ?></td>
                            <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h3>Recent System Activity</h3>
            <?php if (can('activity_logs.view')): ?>
                <a href="<?= url('/admin/activity-logs') ?>" class="btn-brand btn-brand-outline btn-sm-pill">View Logs</a>
            <?php endif; ?>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($recentLogs as $log): ?>
                <div style="padding:10px 12px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);font-size:0.84rem;">
                    <div style="display:flex;justify-content:space-between;gap:8px;">
                        <strong><?= e($log['user_name'] ?: 'System') ?></strong>
                        <small style="color:var(--dash-muted);"><?= e(substr((string) $log['created_at'], 0, 16)) ?></small>
                    </div>
                    <div style="color:var(--dash-muted);margin-top:2px;">
                        <span class="badge-pill badge-neutral" style="font-size:0.66rem;padding:2px 7px;"><?= e($log['module']) ?></span>
                        <?= e($log['description']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
