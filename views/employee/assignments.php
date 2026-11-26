<div class="dash-grid-2">
    <div class="panel-card">
        <h3>My Work Assignments</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($tasks as $t): ?>
                <div style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <strong><?= e($t['title']) ?></strong>
                        <span class="badge-pill <?= status_badge_class($t['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $t['status']))) ?></span>
                    </div>
                    <p style="font-size:0.86rem;color:var(--dash-muted);margin:6px 0 10px;"><?= e($t['description']) ?></p>
                    <?php if ($t['status'] !== 'completed'): ?>
                        <form method="POST" action="<?= url('/employee/assignments/' . (int) $t['id'] . '/status') ?>" style="display:flex;gap:8px;">
                            <?= \Core\Csrf::field() ?>
                            <button type="submit" name="status" value="in_progress" class="btn-brand btn-brand-outline btn-sm-pill">In Progress</button>
                            <button type="submit" name="status" value="completed" class="btn-brand btn-brand-primary btn-sm-pill">Mark Completed</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (empty($tasks)): ?><p style="color:var(--dash-muted);">No assignments right now.</p><?php endif; ?>
        </div>
    </div>

    <div class="panel-card">
        <h3>Assigned Enquiries</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($enquiries as $eq): ?>
                <div style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <div style="display:flex;justify-content:space-between;">
                        <strong><?= e($eq['enquiry_reference']) ?> — <?= e($eq['full_name']) ?></strong>
                        <span class="badge-pill <?= status_badge_class($eq['status']) ?>"><?= e($eq['status']) ?></span>
                    </div>
                    <div style="font-size:0.84rem;color:var(--dash-muted);margin-top:4px;"><?= e($eq['destination_interest'] ?: $eq['subject']) ?></div>
                    <p style="font-size:0.86rem;margin:6px 0 0;"><?= e($eq['message']) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($enquiries)): ?><p style="color:var(--dash-muted);">No assigned enquiries.</p><?php endif; ?>
        </div>
    </div>
</div>
