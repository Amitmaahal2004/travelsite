<div class="dash-grid-2" style="grid-template-columns:0.85fr 1.4fr;">
    <div class="panel-card">
        <h3>Create Custom Role</h3>
        <p style="font-size:0.86rem;color:var(--dash-muted);margin-bottom:16px;">Define custom agency roles and select which portal they sign into.</p>
        <form method="POST" action="<?= url('/admin/roles/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:14px;">
                <label class="form-label-editorial">Role Name *</label>
                <input type="text" name="name" required class="dash-input" placeholder="e.g., Finance Controller">
            </div>
            <div style="margin-bottom:14px;">
                <label class="form-label-editorial">Dashboard Portal *</label>
                <select name="dashboard_prefix" class="dash-select" required>
                    <option value="/admin">Admin Control Center (/admin)</option>
                    <option value="/hr">HR Portal (/hr)</option>
                    <option value="/employee">Specialist Workspace (/employee)</option>
                    <option value="/customer">Customer Portal (/customer)</option>
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label class="form-label-editorial">Description</label>
                <textarea name="description" rows="2" class="dash-textarea"></textarea>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create Custom Role</button>
        </form>
    </div>

    <div class="panel-card">
        <h3>Role Permission Matrix</h3>
        <div style="display:flex;flex-direction:column;gap:22px;">
            <?php foreach ($roles as $role):
                $rid = (int) $role['id'];
                $rolePerms = $assigned[$rid] ?? [];
                $isSuper = $role['slug'] === 'super_admin';
            ?>
                <div style="padding:18px;border-radius:12px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;">
                        <div>
                            <strong style="font-size:1.05rem;"><?= e($role['name']) ?></strong>
                            <span class="badge-pill badge-neutral" style="margin-left:8px;"><?= e($role['dashboard_prefix']) ?></span>
                            <div style="font-size:0.82rem;color:var(--dash-muted);"><?= e($role['description']) ?></div>
                        </div>
                        <?php if (!$role['is_system']): ?>
                            <form method="POST" action="<?= url('/admin/roles/' . $rid . '/delete') ?>" style="margin:0;">
                                <?= \Core\Csrf::field() ?>
                                <button type="submit" data-confirm="Delete role <?= e($role['name']) ?>?" class="btn-brand btn-brand-outline btn-sm-pill">Delete Role</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($isSuper): ?>
                        <div class="badge-pill badge-success">Unrestricted Full Control Across All Modules</div>
                    <?php else: ?>
                        <form method="POST" action="<?= url('/admin/roles/' . $rid . '/permissions') ?>">
                            <?= \Core\Csrf::field() ?>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;margin-bottom:14px;">
                                <?php foreach ($groupedPerms as $module => $perms): ?>
                                    <div style="background:var(--dash-surface);padding:10px 12px;border-radius:8px;border:1px solid var(--dash-border);">
                                        <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--brand-accent);margin-bottom:6px;">
                                            <?= e(str_replace('_', ' ', $module)) ?>
                                        </div>
                                        <?php foreach ($perms as $p): ?>
                                            <label style="display:flex;align-items:center;gap:7px;font-size:0.82rem;margin-bottom:4px;cursor:pointer;">
                                                <input type="checkbox" name="permissions[]" value="<?= (int) $p['id'] ?>"
                                                       <?= in_array((int) $p['id'], $rolePerms, true) ? 'checked' : '' ?>>
                                                <span><?= e($p['action']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Save Permissions for <?= e($role['name']) ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
