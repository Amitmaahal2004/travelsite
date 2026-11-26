<div class="panel-card">
    <div class="panel-head">
        <h2>My Travel Enquiries</h2>
        <a href="<?= url('/custom-enquiry') ?>" class="btn-brand btn-brand-primary btn-sm-pill">New Custom Tour Enquiry</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Type</th><th>Destination / Subject</th><th>Preferred Date</th><th>Status</th><th>Submitted</th></tr></thead>
            <tbody>
                <?php foreach ($enquiries as $eq): ?>
                    <tr>
                        <td><strong><?= e($eq['enquiry_reference']) ?></strong></td>
                        <td><?= e(ucwords(str_replace('_', ' ', $eq['enquiry_type']))) ?></td>
                        <td><?= e($eq['destination_interest'] ?: $eq['subject']) ?></td>
                        <td><?= e($eq['travel_date'] ?: 'Flexible') ?></td>
                        <td><span class="badge-pill <?= status_badge_class($eq['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $eq['status']))) ?></span></td>
                        <td><?= e(substr((string) $eq['created_at'], 0, 10)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($enquiries)): ?><tr><td colspan="6" style="color:var(--dash-muted);">No enquiries submitted yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
