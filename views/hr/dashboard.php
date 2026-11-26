<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Active Workforce</div>
        <div class="kpi-value"><?= (int) $stats['active_employees'] ?> <small style="font-size:0.9rem;color:var(--dash-muted);">/ <?= (int) $stats['total_employees'] ?></small></div>
        <div class="kpi-sub">Across <?= (int) $stats['departments'] ?> departments</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Present Today</div>
        <div class="kpi-value"><?= (int) $stats['present_today'] ?></div>
        <div class="kpi-sub">On-site &amp; remote checked in</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Pending Leaves</div>
        <div class="kpi-value"><?= (int) $stats['pending_leaves'] ?></div>
        <div class="kpi-sub">Awaiting HR decision</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Open Work Assignments</div>
        <div class="kpi-value"><?= (int) $stats['open_assignments'] ?></div>
        <div class="kpi-sub">Active team tasks</div>
    </div>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <div class="panel-head">
            <h3>Pending Leave Requests</h3>
            <a href="<?= url($prefix . '/leaves') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Manage Leaves</a>
        </div>
        <?php if (empty($pendingLeaves)): ?>
            <p style="color:var(--dash-muted);">No pending leave approvals.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($pendingLeaves as $l): ?>
                            <tr>
                                <td><strong><?= e($l['employee_name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($l['department_name']) ?></small></td>
                                <td><?= e(ucfirst($l['leave_type'])) ?></td>
                                <td><?= e($l['start_date']) ?> &rarr; <?= e($l['end_date']) ?></td>
                                <td><?= (int) $l['total_days'] ?></td>
                                <td>
                                    <form method="POST" action="<?= url($prefix . '/leaves/' . (int) $l['id'] . '/decide') ?>" style="display:flex;gap:6px;">
                                        <?= \Core\Csrf::field() ?>
                                        <button type="submit" name="decision" value="approve" class="btn-brand btn-brand-primary btn-sm-pill">Approve</button>
                                        <button type="submit" name="decision" value="reject" class="btn-brand btn-brand-outline btn-sm-pill">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h3>Department Headcount</h3>
            <a href="<?= url($prefix . '/departments') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Departments</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($departments as $d): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <div>
                        <strong><?= e($d['name']) ?></strong>
                        <div style="font-size:0.8rem;color:var(--dash-muted);">Lead: <?= e($d['manager_name'] ?: 'Unassigned') ?></div>
                    </div>
                    <span class="badge-pill badge-info"><?= (int) $d['headcount'] ?> Staff</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="panel-card">
    <div class="panel-head">
        <h3>Today's Attendance Snapshot (<?= date('Y-m-d') ?>)</h3>
        <a href="<?= url($prefix . '/attendance') ?>" class="btn-brand btn-brand-primary btn-sm-pill">Open Attendance Register</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Code</th><th>Employee</th><th>Department</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($todayAttendance as $row): ?>
                    <tr>
                        <td><strong><?= e($row['employee_code']) ?></strong></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['department_name'] ?: '—') ?></td>
                        <td><?= e($row['check_in'] ?: '—') ?></td>
                        <td><?= e($row['check_out'] ?: '—') ?></td>
                        <td><span class="badge-pill <?= status_badge_class($row['attendance_status'] ?? 'neutral') ?>"><?= e(ucwords(str_replace('_', ' ', $row['attendance_status'] ?? 'Not Marked'))) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
