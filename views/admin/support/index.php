<div class="panel-card">
    <h2>Support Tickets</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Ticket #</th><th>Customer</th><th>Subject</th><th>Booking</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td><strong><?= e($t['ticket_number']) ?></strong></td>
                        <td><?= e($t['customer_name']) ?></td>
                        <td><?= e($t['subject']) ?></td>
                        <td><?= e($t['booking_reference'] ?: '—') ?></td>
                        <td><?= e(ucfirst($t['priority'])) ?></td>
                        <td><span class="badge-pill <?= status_badge_class($t['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $t['status']))) ?></span></td>
                        <td><a href="<?= url('/admin/support/' . (int) $t['id']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Respond</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
