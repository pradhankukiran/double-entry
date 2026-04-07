<div class="row">
    <div class="col-lg-8">
        <h4 class="mb-4">My Profile</h4>

        <!-- Profile Information -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Profile Information</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/profile" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="first_name"
                                   name="first_name"
                                   value="<?= \DoubleE\Core\View::e($user['first_name'] ?? '') ?>"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="last_name"
                                   name="last_name"
                                   value="<?= \DoubleE\Core\View::e($user['last_name'] ?? '') ?>"
                                   required
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               value="<?= \DoubleE\Core\View::e($user['email'] ?? '') ?>"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel"
                               class="form-control"
                               id="phone"
                               name="phone"
                               value="<?= \DoubleE\Core\View::e($user['phone'] ?? '') ?>"
                               style="border-radius: 0;">
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Change Password</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/profile/password" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password"
                               class="form-control"
                               id="current_password"
                               name="current_password"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password"
                               class="form-control"
                               id="new_password"
                               name="new_password"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password"
                               class="form-control"
                               id="new_password_confirmation"
                               name="new_password_confirmation"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
