<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>My Leave History</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Remarks</th></tr></thead>
                <tbody>
                    <?php foreach ($leaves as $l): ?>
                        <tr>
                            <td><?= e(ucfirst($l['leave_type'])) ?></td>
                            <td><?= e($l['start_date']) ?> &rarr; <?= e($l['end_date']) ?></td>
                            <td><?= (int) $l['total_days'] ?></td>
                            <td><span class="badge-pill <?= status_badge_class($l['status']) ?>"><?= e(ucfirst($l['status'])) ?></span></td>
                            <td><?= e($l['review_notes'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leaves)): ?><tr><td colspan="5" style="color:var(--dash-muted);">No leave applications yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Apply for Leave</h3>
        <form method="POST" action="<?= url('/employee/leaves/apply') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Leave Type</label>
                <select name="leave_type" class="dash-select">
                    <option value="annual">Annual Leave</option>
                    <option value="sick">Sick Leave</option>
                    <option value="casual">Casual Leave</option>
                    <option value="emergency">Emergency</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div><label class="form-label-editorial">Start Date *</label><input type="date" name="start_date" required class="dash-input"></div>
                <div><label class="form-label-editorial">End Date *</label><input type="date" name="end_date" required class="dash-input"></div>
            </div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Reason *</label><textarea name="reason" rows="3" required class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Submit Request to HR</button>
        </form>
    </div>
</div>
