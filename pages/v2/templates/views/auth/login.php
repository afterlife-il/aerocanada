<div class="aci-auth-card">

    <!-- Brand -->
    <div class="aci-auth-brand">
        <div class="aci-auth-brand-icon">
            <i class="fa-solid fa-plane"></i>
        </div>
        <h1><span style="color:var(--aci-gray-900);">AERO</span><span style="color:var(--aci-red);">CANADA</span></h1>
        <p>Sign in to your ERP account</p>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2" style="font-size:0.85rem;border-radius:var(--aci-border-radius);">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="/pages/v2/index.php?page=login" class="aci-form" id="login-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= \AeroCanada\Core\CSRF::token() ?>">

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label" for="login-email">Email Address</label>
            <div class="aci-input-icon-wrapper">
                <i class="fa-solid fa-envelope input-icon"></i>
                <input
                    type="email"
                    class="form-control"
                    id="login-email"
                    name="email"
                    placeholder="you@aerocanada.com"
                    value="<?= htmlspecialchars($old_email ?? '') ?>"
                    required
                    autofocus
                >
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label" for="login-password">Password</label>
            <div class="aci-input-icon-wrapper">
                <i class="fa-solid fa-lock input-icon"></i>
                <input
                    type="password"
                    class="form-control"
                    id="login-password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>
        </div>

        <!-- Remember Me + Forgot -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember" style="font-size:0.85rem;color:var(--aci-gray-600);">
                    Remember me
                </label>
            </div>
            <a href="#" style="font-size:0.85rem;color:var(--aci-gray-400);pointer-events:none;">Forgot password?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-aci-primary w-100 py-2" id="login-btn">
            <span class="login-text">Sign In</span>
            <span class="login-loading d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Signing in...
            </span>
        </button>
    </form>

    <!-- Footer -->
    <div class="text-center mt-4" style="font-size:0.75rem;color:var(--aci-gray-400);">
        AeroCanada Industries 770 Inc. &mdash; ERP v2.0
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('login-form');
    var btn = document.getElementById('login-btn');

    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            form.classList.add('was-validated');
            return;
        }
        // Show loading state
        btn.querySelector('.login-text').classList.add('d-none');
        btn.querySelector('.login-loading').classList.remove('d-none');
        btn.disabled = true;
    });
});
</script>
