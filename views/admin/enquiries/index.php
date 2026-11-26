<div class="panel-card">
    <div class="panel-head">
        <h2>Customer &amp; Custom Tour Enquiries</h2>
        <div class="filter-bar" style="margin:0;">
            <a href="<?= url('/admin/enquiries') ?>" class="btn-brand <?= !$filterStatus ? 'btn-brand-primary' : 'btn-brand-outline' ?> btn-sm-pill">All</a>
            <?php foreach (['new','in_progress','quoted','converted','closed'] as $st): ?>
                <a href="<?= url('/admin/enquiries?status=' . $st) ?>" class="btn-brand <?= $filterStatus === $st ? 'btn-brand-primary' : 'btn-brand-outline' ?> btn-sm-pill"><?= ucwords(str_replace('_', ' ', $st)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Sender</th><th>Interest / Subject</th><th>Message</th><th>Status &amp; Assignment</th></tr></thead>
            <tbody>
                <?php foreach ($enquiries as $eq): ?>
                    <tr>
                        <td><strong><?= e($eq['enquiry_reference']) ?></strong><br><small><?= e(ucwords(str_replace('_', ' ', $eq['enquiry_type']))) ?></small></td>
                        <td><strong><?= e($eq['full_name']) ?></strong><br><small><?= e($eq['email']) ?><br><?= e($eq['phone']) ?></small></td>
                        <td><?= e($eq['destination_interest'] ?: $eq['subject']) ?><br><small style="color:var(--dash-muted);"><?= e($eq['budget_range'] ?: '') ?> <?= e($eq['travel_date'] ?: '') ?></small></td>
                        <td style="max-width:260px;font-size:0.84rem;"><?= e($eq['message']) ?></td>
                        <td>
                            <form method="POST" action="<?= url('/admin/enquiries/' . (int) $eq['id'] . '/update') ?>" style="display:flex;flex-direction:column;gap:6px;">
                                <?= \Core\Csrf::field() ?>
                                <select name="status" class="dash-select" style="padding:6px 8px;font-size:0.82rem;">
                                    <?php foreach (['new','in_progress','quoted','converted','closed'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $eq['status'] === $st ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="assigned_employee_id" class="dash-select" style="padding:6px 8px;font-size:0.82rem;">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($employees as $e): ?>
                                        <option value="<?= (int) $e['id'] ?>" <?= (int) $eq['assigned_employee_id'] === (int) $e['id'] ? 'selected' : '' ?>><?= e($e['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
