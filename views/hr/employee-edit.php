<div style="margin-bottom:16px;">
    <a href="<?= url($prefix . '/employees') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; Back to Directory</a>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <h3>Edit Profile: <?= e($emp['first_name'] . ' ' . $emp['last_name']) ?> (<?= e($emp['employee_code']) ?>)</h3>
        <form method="POST" action="<?= url($prefix . '/employees/' . (int) $emp['id'] . '/update') ?>">
            <?= \Core\Csrf::field() ?>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div><label class="form-label-editorial">First Name</label><input type="text" name="first_name" required class="dash-input" value="<?= e($emp['first_name']) ?>"></div>
                <div><label class="form-label-editorial">Last Name</label><input type="text" name="last_name" required class="dash-input" value="<?= e($emp['last_name']) ?>"></div>
                <div><label class="form-label-editorial">Phone</label><input type="text" name="phone" class="dash-input" value="<?= e($emp['phone']) ?>"></div>
                <div><label class="form-label-editorial">Designation</label><input type="text" name="designation" required class="dash-input" value="<?= e($emp['designation']) ?>"></div>
                <div>
                    <label class="form-label-editorial">Department</label>
                    <select name="department_id" class="dash-select">
                        <option value="">None</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= (int) $d['id'] ?>" <?= (int) $emp['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Employment Status</label>
                    <select name="status" class="dash-select">
                        <option value="active" <?= $emp['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="on_leave" <?= $emp['status'] === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                        <option value="terminated" <?= $emp['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Employment Type</label>
                    <select name="employment_type" class="dash-select">
                        <option value="full_time" <?= $emp['employment_type'] === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                        <option value="part_time" <?= $emp['employment_type'] === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                        <option value="contract" <?= $emp['employment_type'] === 'contract' ? 'selected' : '' ?>>Contract</option>
                    </select>
                </div>
                <div><label class="form-label-editorial">Performance Score (1-5)</label><input type="number" step="0.1" min="1" max="5" name="performance_score" class="dash-input" value="<?= e($emp['performance_score']) ?>"></div>
                <div><label class="form-label-editorial">Base Salary</label><input type="number" step="0.01" name="base_salary" class="dash-input" value="<?= e($emp['base_salary']) ?>"></div>
                <div><label class="form-label-editorial">Commission %</label><input type="number" step="0.1" name="commission_rate" class="dash-input" value="<?= e($emp['commission_rate']) ?>"></div>
                <?php if ($portal === 'admin'): ?>
                <div>
                    <label class="form-label-editorial">System Role</label>
                    <select name="role_id" class="dash-select">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['id'] ?>" <?= (int) $emp['role_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Emergency Contact</label><input type="text" name="emergency_contact" class="dash-input" value="<?= e($emp['emergency_contact']) ?>"></div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Address</label><input type="text" name="address" class="dash-input" value="<?= e($emp['address']) ?>"></div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">HR Notes</label><textarea name="notes" rows="3" class="dash-textarea"><?= e($emp['notes']) ?></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary">Save Changes</button>
        </form>
    </div>

    <div>
        <div class="panel-card">
            <h3>Employee Documents</h3>
            <?php if (empty($docs)): ?>
                <p style="color:var(--dash-muted);">No documents uploaded yet.</p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($docs as $doc): ?>
                        <li style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--dash-surface-alt);border-radius:8px;">
                            <div>
                                <strong><?= e($doc['title']) ?></strong><br>
                                <small style="color:var(--dash-muted);"><?= e($doc['document_type']) ?> &bull; <?= round((int) $doc['file_size'] / 1024, 1) ?> KB</small>
                            </div>
                            <a href="<?= url($prefix . '/documents/' . (int) $doc['id'] . '/download') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Download</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="panel-card">
            <h3>Recent Work Assignments</h3>
            <?php if (empty($assignments)): ?>
                <p style="color:var(--dash-muted);">No assignments recorded.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($assignments as $a): ?>
                        <div style="padding:10px;background:var(--dash-surface-alt);border-radius:8px;display:flex;justify-content:space-between;">
                            <div><strong><?= e($a['title']) ?></strong><br><small style="color:var(--dash-muted);">Due: <?= e($a['due_date'] ?: 'Flexible') ?></small></div>
                            <span class="badge-pill <?= status_badge_class($a['status']) ?>"><?= e($a['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
