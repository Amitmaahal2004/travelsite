<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Work Assignments</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Task</th><th>Assignee</th><th>Priority</th><th>Due Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td>
                                <strong><?= e($t['title']) ?></strong>
                                <?php if (!empty($t['booking_reference'])): ?><br><small>Ref: <?= e($t['booking_reference']) ?></small><?php endif; ?>
                            </td>
                            <td><?= e($t['employee_name']) ?></td>
                            <td><span class="badge-pill badge-neutral"><?= e(ucfirst($t['priority'])) ?></span></td>
                            <td><?= e($t['due_date'] ?: 'Flexible') ?></td>
                            <td><span class="badge-pill <?= status_badge_class($t['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $t['status']))) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Assign New Work</h3>
        <form method="POST" action="<?= url($prefix . '/assignments/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Assign To *</label>
                <select name="employee_id" required class="dash-select">
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= (int) $e['id'] ?>"><?= e($e['full_name']) ?> (<?= e($e['designation']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Task Title *</label><input type="text" name="title" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">Priority</label>
                    <select name="priority" class="dash-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div><label class="form-label-editorial">Due Date</label><input type="date" name="due_date" class="dash-input"></div>
            </div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Linked Booking (Optional)</label>
                <select name="booking_id" class="dash-select">
                    <option value="">None</option>
                    <?php foreach ($bookings as $b): ?>
                        <option value="<?= (int) $b['id'] ?>"><?= e($b['booking_reference']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Instructions</label><textarea name="description" rows="3" class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Dispatch Task</button>
        </form>
    </div>
</div>
