<h2 class="text-center mb-4">
    <i class="bi bi-person-plus me-2"></i>
    Create Account
</h2>

<form method="POST" action="/auth/register">
    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="first_name" class="form-label">
                    <i class="bi bi-person me-1"></i>
                    First Name
                </label>
                <input type="text" 
                       class="form-control <?= session_has_error('first_name') ? 'is-invalid' : '' ?>" 
                       id="first_name" 
                       name="first_name" 
                       value="<?= old_input('first_name') ?>"
                       placeholder="Enter your first name"
                       required>
                <?php if (session_has_error('first_name')): ?>
                    <div class="invalid-feedback">
                        <?= session_get_error('first_name') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="last_name" class="form-label">
                    <i class="bi bi-person me-1"></i>
                    Last Name
                </label>
                <input type="text" 
                       class="form-control <?= session_has_error('last_name') ? 'is-invalid' : '' ?>" 
                       id="last_name" 
                       name="last_name" 
                       value="<?= old_input('last_name') ?>"
                       placeholder="Enter your last name"
                       required>
                <?php if (session_has_error('last_name')): ?>
                    <div class="invalid-feedback">
                        <?= session_get_error('last_name') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
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
        <label for="phone" class="form-label">
            <i class="bi bi-telephone me-1"></i>
            Phone Number
        </label>
        <input type="tel" 
               class="form-control <?= session_has_error('phone') ? 'is-invalid' : '' ?>" 
               id="phone" 
               name="phone" 
               value="<?= old_input('phone') ?>"
               placeholder="Enter your phone number"
               required>
        <?php if (session_has_error('phone')): ?>
            <div class="invalid-feedback">
                <?= session_get_error('phone') ?>
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
               placeholder="Enter a strong password"
               minlength="8"
               required>
        <?php if (session_has_error('password')): ?>
            <div class="invalid-feedback">
                <?= session_get_error('password') ?>
            </div>
        <?php endif; ?>
        <div class="form-text">
            Password must be at least 8 characters long
        </div>
    </div>
    
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">
            <i class="bi bi-lock-fill me-1"></i>
            Confirm Password
        </label>
        <input type="password" 
               class="form-control" 
               id="password_confirmation" 
               name="password_confirmation" 
               placeholder="Confirm your password"
               minlength="8"
               required>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
        <label class="form-check-label" for="terms">
            I agree to the <a href="#" target="_blank">Terms of Service</a> and 
            <a href="#" target="_blank">Privacy Policy</a>
        </label>
    </div>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-person-plus me-1"></i>
            Create Account
        </button>
    </div>
</form>

<div class="auth-links">
    <p class="mb-0">
        Already have an account? 
        <a href="/auth/login">
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Sign in here
        </a>
    </p>
</div>