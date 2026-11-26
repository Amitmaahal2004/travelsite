<div class="panel-card">
    <div class="panel-head">
        <h2>My Notifications</h2>
        <form method="POST" action="<?= url('/customer/notifications') ?>" style="margin:0;">
            <?= \Core\Csrf::field() ?>
            <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Mark All Read</button>
        </form>
    </div>
    <?php foreach ($notifications as $n): ?>
        <div style="padding:12px;border-radius:10px;background:var(--dash-surface-alt);margin-bottom:8px;opacity:<?= $n['is_read'] ? '0.68' : '1' ?>;">
            <strong><?= e($n['title']) ?></strong> <small style="color:var(--dash-muted);float:right;"><?= e($n['created_at']) ?></small>
            <p style="margin:4px 0 0;color:var(--dash-muted);font-size:0.88rem;"><?= e($n['message']) ?></p>
        </div>
    <?php endforeach; ?>
    <?php if (empty($notifications)): ?><p style="color:var(--dash-muted);">No notifications yet.</p><?php endif; ?>
</div>
