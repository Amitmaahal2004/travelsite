<span class="section-eyebrow">Account Recovery</span>
<h2 style="font-size:2rem;color:var(--brand-primary);margin-bottom:6px;">Forgot Password</h2>
<p style="color:var(--ink-soft);font-size:0.9rem;margin-bottom:20px;">Enter your email address to receive a secure password reset link.</p>

<form method="POST" action="<?= url('/forgot-password') ?>">
    <?= \Core\Csrf::field() ?>
    <div style="margin-bottom:18px;">
        <label class="form-label-editorial">Email Address</label>
        <input type="email" name="email" required class="form-control-editorial" placeholder="name@example.com">
    </div>
    <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Send Reset Link</button>
</form>

<p style="text-align:center;font-size:0.88rem;color:var(--ink-muted);margin:18px 0 0;">
    Remembered it? <a href="<?= url('/login') ?>" style="color:var(--brand-primary);font-weight:700;">Back to Sign In</a>
</p>
