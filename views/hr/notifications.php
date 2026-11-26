<div class="panel-card">
    <div class="panel-head">
        <h2>HR Notifications</h2>
        <form method="POST" action="<?= url($prefix . '/notifications') ?>" style="margin:0;">
            <?= \Core\Csrf::field() ?>
            <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Mark All Read</button>
        </form>
    </div>
    <?php if (empty($notifications)): ?>
        <p style="color:var(--dash-muted);">No notifications yet.</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($notifications as $n): ?>
                <div style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);opacity:<?= $n['is_read'] ? '0.68' : '1' ?>;">
                    <div style="display:flex;justify-content:space-between;">
                        <strong><?= e($n['title']) ?></strong>
                        <small style="color:var(--dash-muted);"><?= e($n['created_at']) ?></small>
                    </div>
                    <p style="margin:6px 0 0;color:var(--dash-muted);font-size:0.9rem;"><?= e($n['message']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
