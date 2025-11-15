<h2 class="text-center mb-4">
    <i class="bi bi-box-arrow-in-right me-2"></i>
    Sign In
</h2>

<form method="POST" action="/auth/login">
    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
    
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-1"></i>
            Email Address
        </label>
        <input type="email" 
               class="form-control <?= session_has_error('email') ? 'is-invalid' : '' ?>" 
               id="email" 
               name="email" 
               value="<?= old_input('email') ?>"
               placeholder="Enter your email address"
               required>
        <?php if (session_has_error('email')): ?>
            <div class="invalid-feedback">
                <?= session_get_error('email') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-1"></i>
            Password
        </label>
        <input type="password" 
               class="form-control <?= session_has_error('password') ? 'is-invalid' : '' ?>" 
               id="password" 
               name="password" 
               placeholder="Enter your password"
               required>
        <?php if (session_has_error('password')): ?>
            <div class="invalid-feedback">
                <?= session_get_error('password') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">
            Remember me
        </label>
    </div>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Sign In
        </button>
    </div>
</form>

<div class="auth-links">
    <p class="mb-2">
        <a href="/auth/forgot-password">
            <i class="bi bi-question-circle me-1"></i>
            Forgot your password?
        </a>
    </p>
    <p class="mb-0">
        Don't have an account? 
        <a href="/auth/register">
            <i class="bi bi-person-plus me-1"></i>
            Sign up here
        </a>
    </p>
</div>