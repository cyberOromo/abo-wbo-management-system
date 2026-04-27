<?php $errorMessage = $errorMessage ?? 'The page you are looking for could not be found.'; ?>

<div class="text-center">
    <div class="error-page">
        <div class="error-code">
            <h1 class="display-1 text-danger">404</h1>
        </div>
        <div class="error-content">
            <h2 class="mb-4">Page Not Found</h2>
            <p class="lead mb-4">
                <?= htmlspecialchars($errorMessage) ?>
            </p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary me-2">
                    <i class="bi bi-house me-1"></i>
                    Go Home
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Go Back
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .error-page {
        padding: 4rem 0;
    }
    
    .error-code h1 {
        font-size: 8rem;
        font-weight: 300;
        text-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .error-content h2 {
        font-weight: 600;
        color: #343a40;
    }
    
    .error-actions {
        margin-top: 2rem;
    }
</style>