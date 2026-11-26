<div class="panel-card">
    <div class="panel-head">
        <div>
            <h2>My Attendance</h2>
            <p style="margin:4px 0 0;color:var(--dash-muted);font-size:0.9rem;">Today: <?= date('l, F j, Y') ?></p>
        </div>
        <form method="POST" action="<?= url('/employee/attendance/toggle') ?>" style="margin:0;">
            <?= \Core\Csrf::field() ?>
            <?php if (!$today): ?>
                <button type="submit" class="btn-brand btn-brand-primary">Clock In Now</button>
            <?php elseif (empty($today['check_out'])): ?>
                <button type="submit" class="btn-brand btn-brand-accent">Clock Out (In at <?= e($today['check_in']) ?>)</button>
            <?php else: ?>
                <span class="badge-pill badge-success">Completed Today (<?= e($today['check_in']) ?> - <?= e($today['check_out']) ?>)</span>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Notes</th></tr></thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td><strong><?= e($h['attendance_date']) ?></strong></td>
                        <td><?= e($h['check_in'] ?: '—') ?></td>
                        <td><?= e($h['check_out'] ?: '—') ?></td>
                        <td><span class="badge-pill <?= status_badge_class($h['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $h['status']))) ?></span></td>
                        <td><?= e($h['notes'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
