<span class="section-eyebrow">Security Update</span>
<h2 style="font-size:2rem;color:var(--brand-primary);margin-bottom:6px;">Set New Password</h2>
<p style="color:var(--ink-soft);font-size:0.9rem;margin-bottom:20px;">Choose a strong password of at least 8 characters.</p>

<form method="POST" action="<?= url('/reset-password') ?>">
    <?= \Core\Csrf::field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div style="margin-bottom:14px;">
        <label class="form-label-editorial">New Password</label>
        <input type="password" name="password" required minlength="8" class="form-control-editorial">
    </div>
    <div style="margin-bottom:18px;">
        <label class="form-label-editorial">Confirm Password</label>
        <input type="password" name="password_confirmation" required minlength="8" class="form-control-editorial">
    </div>
    <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Update Password</button>
</form>
