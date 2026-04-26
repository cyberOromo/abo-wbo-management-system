<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Complete Registration - ABO-WBO' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/hybrid-registration.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="registration-body">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Panel - Progress & Info -->
            <div class="col-lg-4 registration-left-panel d-flex align-items-center">
                <div class="registration-progress-panel w-100">
                    <div class="text-center mb-4">
                        <img src="/assets/images/abo-wbo-logo.png" alt="ABO-WBO" class="registration-logo-sm mb-3">
                        <h4>Complete Your Registration</h4>
                    </div>
                    
                    <!-- Verified Email Display -->
                    <div class="verified-email-card mb-4">
                        <div class="verified-email-header">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Email Verified</span>
                        </div>
                        <div class="verified-email-address">
                            <?= htmlspecialchars($registration['personal_email']) ?>
                        </div>
                    </div>
                    
                    <!-- Registration Steps -->
                    <div class="completion-checklist">
                        <h6>Complete These Steps:</h6>
                        <div class="checklist-item completed">
                            <i class="fas fa-check-circle"></i>
                            <span>Email Verification</span>
                        </div>
                        <div class="checklist-item active">
                            <i class="fas fa-user-edit"></i>
                            <span>Personal Information</span>
                        </div>
                        <div class="checklist-item">
                            <i class="fas fa-sitemap"></i>
                            <span>Organization Placement</span>
                        </div>
                        <div class="checklist-item">
                            <i class="fas fa-user-check"></i>
                            <span>Leadership Approval</span>
                        </div>
                        <div class="checklist-item">
                            <i class="fas fa-envelope-open-text"></i>
                            <span>Internal Email Creation</span>
                        </div>
                    </div>
                    
                    <!-- Internal Email Preview -->
                    <div class="email-preview-card mt-4">
                        <h6>Your Internal Email Preview</h6>
                        <div class="email-preview-display">
                            <span id="emailPreviewDisplay">Will be generated based on your information</span>
                        </div>
                        <small class="text-muted">Primary login: firstname.lastInitial@j-abo-wbo.org. Position-and-hierarchy aliases are added later for executive or admin roles when applicable.</small>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Registration Form -->
            <div class="col-lg-8 registration-right-panel">
                <div class="registration-form-container">
                    
                    <!-- Form Header -->
                    <div class="form-header mb-4">
                        <h2>Personal & Organizational Information</h2>
                        <p class="text-muted">Please provide accurate information for your organizational registration.</p>
                    </div>
                    
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>
                    
                    <!-- Registration Form -->
                    <form id="completeRegistrationForm" class="registration-form">
                        <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">
                        
                        <!-- Personal Information Section -->
                        <div class="form-section mb-5">
                            <div class="section-header">
                                <h4><i class="fas fa-user me-2"></i>Personal Information</h4>
                                <hr>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="firstName" class="form-label required">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" 
                                               placeholder="Enter your first name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="lastName" class="form-label required">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" 
                                               placeholder="Enter your last name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label required">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="+1234567890" required>
                                        <div class="form-text">Include country code (e.g., +251 for Ethiopia)</div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="dateOfBirth" class="form-label required">Date of Birth</label>
                                        <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="gender" class="form-label required">Gender</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organizational Placement Section -->
                        <div class="form-section mb-5">
                            <div class="section-header">
                                <h4><i class="fas fa-sitemap me-2"></i>Organizational Placement</h4>
                                <hr>
                            </div>
                            
                            <div class="hierarchy-selection mb-4">
                                <label class="form-label required">Select Your Organizational Level</label>
                                <div class="hierarchy-options">
                                    <div class="hierarchy-option" data-level="global">
                                        <div class="hierarchy-card">
                                            <input type="radio" id="levelGlobal" name="target_hierarchy_level" value="global">
                                            <label for="levelGlobal" class="hierarchy-label">
                                                <div class="hierarchy-icon">
                                                    <i class="fas fa-globe"></i>
                                                </div>
                                                <div class="hierarchy-info">
                                                    <h6>Global Level</h6>
                                                    <p>Organization-wide positions and leadership</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="hierarchy-option" data-level="godina">
                                        <div class="hierarchy-card">
                                            <input type="radio" id="levelGodina" name="target_hierarchy_level" value="godina">
                                            <label for="levelGodina" class="hierarchy-label">
                                                <div class="hierarchy-icon">
                                                    <i class="fas fa-city"></i>
                                                </div>
                                                <div class="hierarchy-info">
                                                    <h6>Godina Level</h6>
                                                    <p>Regional administrative units</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="hierarchy-option" data-level="gamta">
                                        <div class="hierarchy-card">
                                            <input type="radio" id="levelGamta" name="target_hierarchy_level" value="gamta">
                                            <label for="levelGamta" class="hierarchy-label">
                                                <div class="hierarchy-icon">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div class="hierarchy-info">
                                                    <h6>Gamta Level</h6>
                                                    <p>District-level operations</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="hierarchy-option" data-level="gurmu">
                                        <div class="hierarchy-card">
                                            <input type="radio" id="levelGurmu" name="target_hierarchy_level" value="gurmu">
                                            <label for="levelGurmu" class="hierarchy-label">
                                                <div class="hierarchy-icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="hierarchy-info">
                                                    <h6>Gurmu Level</h6>
                                                    <p>Community-level groups</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Specific Hierarchy Selection -->
                            <div class="specific-hierarchy-selection" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label id="hierarchySelectLabel" class="form-label required">Select Specific Unit</label>
                                            <select class="form-select" id="hierarchySelect" name="target_hierarchy_id">
                                                <option value="">Loading...</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Position Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="targetPosition" class="form-label">Target Position</label>
                                        <select class="form-select" id="targetPosition" name="target_position_id">
                                            <option value="">Select Position (Optional)</option>
                                            <?php foreach ($positions as $position): ?>
                                                <option value="<?= $position['id'] ?>" data-key="<?= $position['key_name'] ?>">
                                                    <?= htmlspecialchars($position['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Leave blank for general membership</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information Section -->
                        <div class="form-section mb-5">
                            <div class="section-header">
                                <h4><i class="fas fa-info-circle me-2"></i>Additional Information</h4>
                                <hr>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="additionalInfo" class="form-label">Additional Information</label>
                                <textarea class="form-control" id="additionalInfo" name="additional_info" 
                                          rows="4" placeholder="Any additional information you'd like to provide (optional)"></textarea>
                                <div class="form-text">This information will be reviewed by the approval committee</div>
                            </div>
                        </div>
                        
                        <!-- Summary Section -->
                        <div class="registration-summary" style="display: none;">
                            <div class="section-header">
                                <h4><i class="fas fa-eye me-2"></i>Registration Summary</h4>
                                <hr>
                            </div>
                            
                            <div class="summary-content">
                                <div class="summary-item">
                                    <strong>Personal Email:</strong> 
                                    <span><?= htmlspecialchars($registration['personal_email']) ?></span>
                                </div>
                                <div class="summary-item">
                                    <strong>Full Name:</strong> 
                                    <span id="summaryName">-</span>
                                </div>
                                <div class="summary-item">
                                    <strong>Organizational Level:</strong> 
                                    <span id="summaryLevel">-</span>
                                </div>
                                <div class="summary-item">
                                    <strong>Target Position:</strong> 
                                    <span id="summaryPosition">General Member</span>
                                </div>
                                <div class="summary-item">
                                    <strong>Internal Email Preview:</strong> 
                                    <span id="summaryEmail" class="text-primary">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" id="previewRegistration" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-eye me-2"></i>
                                        Preview Registration
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Submit Registration
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Processing...</span>
            </div>
            <p class="mt-3">Processing your registration...</p>
        </div>
    </div>
    
    <!-- Hierarchy Data for JavaScript -->
    <script>
        window.hierarchyData = {
            globals: <?= json_encode($hierarchies['globals'] ?? []) ?>,
            godinas: <?= json_encode($hierarchies['godinas'] ?? []) ?>,
            gamtas: <?= json_encode($hierarchies['gamtas'] ?? []) ?>,
            gurmus: <?= json_encode($hierarchies['gurmus'] ?? []) ?>
        };
    </script>
    
    <!-- Scripts -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/hybrid-registration-complete.js"></script>
</body>
</html>