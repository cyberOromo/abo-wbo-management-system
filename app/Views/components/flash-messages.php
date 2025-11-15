<!-- Flash Messages Component -->
<?php if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])): ?>
    <div class="flash-messages-container mb-4">
        <?php foreach ($_SESSION['flash_messages'] as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
                    <!-- Alert Icon -->
                    <?php
                    $icons = [
                        'success' => 'check-circle-fill',
                        'error' => 'exclamation-triangle-fill',
                        'warning' => 'exclamation-triangle-fill',
                        'info' => 'info-circle-fill',
                        'primary' => 'info-circle-fill',
                        'secondary' => 'info-circle-fill',
                        'danger' => 'exclamation-triangle-fill'
                    ];
                    $icon = $icons[$type] ?? 'info-circle-fill';
                    ?>
                    <i class="bi bi-<?= $icon ?> me-2" aria-hidden="true"></i>
                    
                    <!-- Alert Content -->
                    <div class="alert-content">
                        <?php if (is_array($message)): ?>
                            <?php if (isset($message['title'])): ?>
                                <div class="alert-title fw-bold"><?= htmlspecialchars($message['title']) ?></div>
                            <?php endif; ?>
                            <div class="alert-message"><?= htmlspecialchars($message['message'] ?? $message['text'] ?? '') ?></div>
                            <?php if (isset($message['details']) && !empty($message['details'])): ?>
                                <div class="alert-details mt-2">
                                    <small class="text-muted"><?= htmlspecialchars($message['details']) ?></small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert-message"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Dismiss Button -->
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    
    <?php
    // Clear flash messages after displaying
    unset($_SESSION['flash_messages']);
    ?>
<?php endif; ?>

<!-- Toast Messages (for AJAX responses) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container">
    <!-- Dynamic toasts will be inserted here -->
</div>

<script>
// Toast notification function
function showToast(message, type = 'info', title = '', duration = 5000) {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;
    
    // Generate unique ID for this toast
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    
    // Icon mapping
    const icons = {
        success: 'check-circle-fill',
        error: 'exclamation-triangle-fill',
        warning: 'exclamation-triangle-fill',
        info: 'info-circle-fill',
        primary: 'info-circle-fill',
        secondary: 'info-circle-fill',
        danger: 'exclamation-triangle-fill'
    };
    
    // Color mapping for Bootstrap
    const colors = {
        error: 'danger',
        warning: 'warning',
        success: 'success',
        info: 'info',
        primary: 'primary',
        secondary: 'secondary',
        danger: 'danger'
    };
    
    const icon = icons[type] || 'info-circle-fill';
    const color = colors[type] || 'info';
    
    // Create toast HTML
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-bg-${color} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi bi-${icon} me-2" aria-hidden="true"></i>
                    <div class="flex-grow-1">
                        ${title ? `<div class="fw-bold">${title}</div>` : ''}
                        <div>${message}</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        delay: duration
    });
    
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
    
    return toast;
}

// Global function to show flash messages via JavaScript
window.showFlashMessage = showToast;

// Auto-dismiss flash alerts after specified time
document.addEventListener('DOMContentLoaded', function() {
    const flashAlerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    flashAlerts.forEach(function(alert) {
        const timeout = parseInt(alert.dataset.autoDismiss) || 5000;
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, timeout);
    });
});
</script>

<style>
/* Flash Messages Styling */
.flash-messages-container {
    position: relative;
    z-index: 1050;
}

.flash-messages-container .alert {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 0.75rem;
}

.flash-messages-container .alert:last-child {
    margin-bottom: 0;
}

.alert-content {
    flex-grow: 1;
}

.alert-title {
    margin-bottom: 0.25rem;
}

.alert-message {
    margin-bottom: 0;
}

.alert-details {
    font-size: 0.875rem;
    opacity: 0.8;
}

/* Toast Container Styling */
.toast-container {
    z-index: 1060;
}

.toast {
    min-width: 300px;
    max-width: 400px;
}

.toast .toast-body {
    padding: 0.75rem;
}

/* Animation for flash messages */
.alert {
    animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .flash-messages-container .alert {
    box-shadow: 0 0.125rem 0.25rem rgba(255, 255, 255, 0.075);
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .toast {
        min-width: 280px;
        max-width: calc(100vw - 2rem);
    }
    
    .flash-messages-container .alert {
        border-radius: 0.375rem;
    }
}

/* Accessibility improvements */
.alert-dismissible .btn-close {
    padding: 0.75rem;
}

.alert-dismissible .btn-close:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}

/* Print styles */
@media print {
    .flash-messages-container,
    .toast-container {
        display: none !important;
    }
}
</style>