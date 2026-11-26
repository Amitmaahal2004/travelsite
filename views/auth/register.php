<span class="section-eyebrow">Traveller Membership</span>
<h2 style="font-size:2rem;color:var(--brand-primary);margin-bottom:6px;">Create Account</h2>
<p style="color:var(--ink-soft);font-size:0.9rem;margin-bottom:20px;">Manage bookings, download PDF invoices, and save destinations.</p>

<form method="POST" action="<?= url('/register') ?>">
    <?= \Core\Csrf::field() ?>
    <div class="form-row-2" style="margin-bottom:14px;">
        <div>
            <label class="form-label-editorial">First Name *</label>
            <input type="text" name="first_name" required class="form-control-editorial" value="<?= e(old('first_name')) ?>">
        </div>
        <div>
            <label class="form-label-editorial">Last Name *</label>
            <input type="text" name="last_name" required class="form-control-editorial" value="<?= e(old('last_name')) ?>">
        </div>
    </div>
    <div style="margin-bottom:14px;">
        <label class="form-label-editorial">Email Address *</label>
        <input type="email" name="email" required class="form-control-editorial" value="<?= e(old('email')) ?>">
    </div>
    <div style="margin-bottom:14px;">
        <label class="form-label-editorial">Phone Number *</label>
        <input type="text" name="phone" required class="form-control-editorial" value="<?= e(old('phone')) ?>">
    </div>
    <div class="form-row-2" style="margin-bottom:18px;">
        <div>
            <label class="form-label-editorial">Password *</label>
            <input type="password" name="password" required minlength="8" class="form-control-editorial" placeholder="Min 8 characters">
        </div>
        <div>
            <label class="form-label-editorial">Confirm Password *</label>
            <input type="password" name="password_confirmation" required minlength="8" class="form-control-editorial">
        </div>
    </div>
    <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create Account</button>
</form>

<p style="text-align:center;font-size:0.88rem;color:var(--ink-muted);margin:18px 0 0;">
    Already registered? <a href="<?= url('/login') ?>" style="color:var(--brand-primary);font-weight:700;">Sign In</a>
</p>
