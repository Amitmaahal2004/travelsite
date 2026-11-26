<span class="section-eyebrow">Portal Access</span>
<h2 style="font-size:2rem;color:var(--brand-primary);margin-bottom:6px;">Sign In</h2>
<p style="color:var(--ink-soft);font-size:0.9rem;margin-bottom:22px;">Access your Customer, Specialist, HR, or Admin workspace.</p>

<form method="POST" action="<?= url('/login') ?>">
    <?= \Core\Csrf::field() ?>
    <div style="margin-bottom:16px;">
        <label class="form-label-editorial">Email Address</label>
        <input type="email" name="email" required autofocus class="form-control-editorial" value="<?= e(old('email')) ?>" placeholder="name@example.com">
    </div>
    <div style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <label class="form-label-editorial">Password</label>
            <a href="<?= url('/forgot-password') ?>" style="font-size:0.8rem;color:var(--brand-primary);font-weight:600;">Forgot password?</a>
        </div>
        <input type="password" name="password" required class="form-control-editorial" placeholder="Enter your password">
    </div>
    <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;margin-top:6px;">Sign In</button>
</form>

<p style="text-align:center;font-size:0.88rem;color:var(--ink-muted);margin:18px 0 0;">
    New traveller? <a href="<?= url('/register') ?>" style="color:var(--brand-primary);font-weight:700;">Create Customer Account</a>
</p>

<div style="margin-top:22px;padding:14px;border-radius:10px;background:var(--ivory);border:1px solid var(--border-hairline);font-size:0.78rem;color:var(--ink-muted);">
    <strong style="display:block;color:var(--brand-primary);margin-bottom:4px;">Seeded Demo Accounts (Local Testing)</strong>
    <div><strong>Super Admin:</strong> admin@travelplatform.local / Admin@123</div>
    <div><strong>HR Manager:</strong> hr@travelplatform.local / Hr@12345</div>
    <div><strong>Employee:</strong> staff@travelplatform.local / Staff@123</div>
    <div><strong>Customer:</strong> customer@travelplatform.local / Traveler@123</div>
</div>
