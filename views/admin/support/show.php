<div style="margin-bottom:14px;"><a href="<?= url('/admin/support') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; All Tickets</a></div>
<div class="panel-card">
    <div class="panel-head">
        <div>
            <h2><?= e($ticket['ticket_number']) ?> — <?= e($ticket['subject']) ?></h2>
            <small style="color:var(--dash-muted);">Customer: <?= e($ticket['customer_name']) ?> (<?= e($ticket['customer_email']) ?>)</small>
        </div>
        <span class="badge-pill <?= status_badge_class($ticket['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $ticket['status']))) ?></span>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
        <?php foreach ($replies as $rep): ?>
            <div style="padding:12px 14px;border-radius:10px;background:<?= $rep['is_staff'] ? 'rgba(var(--brand-accent-rgb),0.12)' : 'var(--dash-surface-alt)' ?>;">
                <small style="color:var(--dash-muted);"><?= e($rep['author_name']) ?> &bull; <?= e($rep['created_at']) ?></small>
                <div><?= nl2br(e($rep['message'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="<?= url('/admin/support/' . (int) $ticket['id'] . '/reply') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="form-grid-2" style="margin-bottom:12px;">
            <div>
                <label class="form-label-editorial">Update Status</label>
                <select name="status" class="dash-select">
                    <?php foreach (['open','in_progress','resolved','closed'] as $st): ?>
                        <option value="<?= $st ?>" <?= $ticket['status'] === $st ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <textarea name="message" rows="3" class="dash-textarea" style="margin-bottom:12px;" placeholder="Reply to customer..."></textarea>
        <button type="submit" class="btn-brand btn-brand-primary">Send Staff Reply</button>
    </form>
</div>
