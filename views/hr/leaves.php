<div class="panel-card">
    <div class="panel-head">
        <h2>Leave Requests &amp; Approvals</h2>
        <div class="filter-bar" style="margin:0;">
            <a href="<?= url($prefix . '/leaves') ?>" class="btn-brand <?= !$filterStatus ? 'btn-brand-primary' : 'btn-brand-outline' ?> btn-sm-pill">All</a>
            <a href="<?= url($prefix . '/leaves?status=pending') ?>" class="btn-brand <?= $filterStatus === 'pending' ? 'btn-brand-primary' : 'btn-brand-outline' ?> btn-sm-pill">Pending</a>
            <a href="<?= url($prefix . '/leaves?status=approved') ?>" class="btn-brand <?= $filterStatus === 'approved' ? 'btn-brand-primary' : 'btn-brand-outline' ?> btn-sm-pill">Approved</a>
            <a href="<?= url($prefix . '/leaves?status=rejected') ?>" class="btn-brand <?= $filterStatus === 'rejected' ? 'btn-brand-primary' : 'btn-brand-outline' ?> btn-sm-pill">Rejected</a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Decision</th></tr></thead>
            <tbody>
                <?php foreach ($leaves as $l): ?>
                    <tr>
                        <td><strong><?= e($l['employee_name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($l['department_name']) ?></small></td>
                        <td><?= e(ucfirst($l['leave_type'])) ?></td>
                        <td><?= e($l['start_date']) ?> &rarr; <?= e($l['end_date']) ?><br><small><?= (int) $l['total_days'] ?> day(s)</small></td>
                        <td style="max-width:280px;"><?= e($l['reason']) ?></td>
                        <td><span class="badge-pill <?= status_badge_class($l['status']) ?>"><?= e(ucfirst($l['status'])) ?></span></td>
                        <td>
                            <?php if ($l['status'] === 'pending'): ?>
                                <form method="POST" action="<?= url($prefix . '/leaves/' . (int) $l['id'] . '/decide') ?>" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                                    <?= \Core\Csrf::field() ?>
                                    <input type="text" name="review_notes" class="dash-input" style="width:140px;padding:6px 10px;" placeholder="Remarks...">
                                    <button type="submit" name="decision" value="approve" class="btn-brand btn-brand-primary btn-sm-pill">Approve</button>
                                    <button type="submit" name="decision" value="reject" class="btn-brand btn-brand-outline btn-sm-pill">Reject</button>
                                </form>
                            <?php else: ?>
                                <small style="color:var(--dash-muted);"><?= e($l['reviewer_name'] ?: 'System') ?>: <?= e($l['review_notes'] ?: 'Processed') ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
