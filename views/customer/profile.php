<div class="dash-grid-2">
    <div class="panel-card">
        <h3>Personal Information &amp; Travel Profile</h3>
        <form method="POST" action="<?= url('/customer/profile') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
                <img id="avatar-prev" src="<?= e($cust['avatar'] ? upload_url($cust['avatar']) : brand_logo('primary')) ?>" alt="Profile Photo"
                     style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:2px solid var(--brand-accent);">
                <div>
                    <label class="form-label-editorial">Profile Photo</label>
                    <input type="file" name="avatar" accept="image/*" data-image-preview="#avatar-prev" class="dash-input" style="font-size:0.82rem;">
                </div>
            </div>

            <div class="form-grid-2" style="margin-bottom:12px;">
                <div><label class="form-label-editorial">First Name *</label><input type="text" name="first_name" required class="dash-input" value="<?= e($cust['first_name']) ?>"></div>
                <div><label class="form-label-editorial">Last Name *</label><input type="text" name="last_name" required class="dash-input" value="<?= e($cust['last_name']) ?>"></div>
                <div><label class="form-label-editorial">Email (Read-Only)</label><input type="email" class="dash-input" value="<?= e($cust['email']) ?>" disabled></div>
                <div><label class="form-label-editorial">Phone</label><input type="text" name="phone" class="dash-input" value="<?= e($cust['phone']) ?>"></div>
                <div><label class="form-label-editorial">Date of Birth</label><input type="date" name="date_of_birth" class="dash-input" value="<?= e($cust['date_of_birth']) ?>"></div>
                <div><label class="form-label-editorial">Nationality</label><input type="text" name="nationality" class="dash-input" value="<?= e($cust['nationality']) ?>"></div>
                <div><label class="form-label-editorial">Passport Number</label><input type="text" name="passport_number" class="dash-input" value="<?= e($cust['passport_number']) ?>"></div>
                <div><label class="form-label-editorial">Passport Expiry</label><input type="date" name="passport_expiry" class="dash-input" value="<?= e($cust['passport_expiry']) ?>"></div>
                <div><label class="form-label-editorial">City</label><input type="text" name="city" class="dash-input" value="<?= e($cust['city']) ?>"></div>
                <div><label class="form-label-editorial">Country</label><input type="text" name="country" class="dash-input" value="<?= e($cust['country']) ?>"></div>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Street Address</label><input type="text" name="address" class="dash-input" value="<?= e($cust['address']) ?>"></div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Travel Preferences (Dining, Bedding, Airlines)</label><textarea name="travel_preferences" rows="3" class="dash-textarea"><?= e($cust['travel_preferences']) ?></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary">Save Travel Profile</button>
        </form>
    </div>

    <div class="panel-card">
        <h3>Change Password</h3>
        <form method="POST" action="<?= url('/customer/password') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Current Password *</label><input type="password" name="current_password" required class="dash-input"></div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">New Password (min 8 chars) *</label><input type="password" name="new_password" required minlength="8" class="dash-input"></div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Confirm New Password *</label><input type="password" name="confirm_password" required minlength="8" class="dash-input"></div>
            <button type="submit" class="btn-brand btn-brand-primary">Update Password</button>
        </form>
    </div>
</div>
