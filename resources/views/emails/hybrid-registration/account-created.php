<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ABO-WBO - Account Created Successfully</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        /* Container */
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            backdrop-filter: blur(10px);
        }
        
        .header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Content */
        .email-content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .welcome-message {
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.7;
            color: #495057;
        }
        
        /* Account Details Card */
        .account-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border-left: 5px solid #28a745;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .account-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .account-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-right: 20px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .account-info h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .account-role {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .account-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        
        .detail-group {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .detail-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .email-badge {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }
        
        /* Credentials Section */
        .credentials-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .credentials-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .credentials-icon {
            width: 50px;
            height: 50px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #212529;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .credentials-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #856404;
        }
        
        .credentials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .credential-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        
        .credential-label {
            font-size: 0.85rem;
            color: #856404;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .credential-value {
            font-size: 1rem;
            color: #212529;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
            min-width: 140px;
        }
        
        /* Hierarchy Display */
        .hierarchy-section {
            background: white;
            border: 2px solid #007bff;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        
        .hierarchy-title {
            font-size: 1.1rem;
            color: #495057;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .hierarchy-path {
            font-size: 1.3rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .hierarchy-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Action Buttons */
        .action-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .action-button {
            display: inline-block;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            min-width: 160px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-profile {
            background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
            color: white;
        }
        
        .btn-help {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: white;
        }
        
        /* Next Steps */
        .next-steps {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .next-steps-title {
            font-weight: 700;
            color: #0c5460;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }
        
        .next-steps-title::before {
            content: "🚀";
            margin-right: 12px;
            font-size: 1.3rem;
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .step-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: #17a2b8;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 15px;
        }
        
        .step-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .step-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Security Notice */
        .security-notice {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .security-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .security-icon {
            width: 50px;
            height: 50px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .security-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #721c24;
        }
        
        .security-text {
            color: #721c24;
            line-height: 1.6;
        }
        
        /* Footer */
        .email-footer {
            background: #2c3e50;
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .footer-welcome {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .footer-text {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .footer-links {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .footer-link {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            opacity: 0.8;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .footer-link:hover {
            opacity: 1;
            color: #20c997;
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .account-header {
                flex-direction: column;
                text-align: center;
            }
            
            .account-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .account-details {
                grid-template-columns: 1fr;
            }
            
            .credentials-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-button {
                width: 100%;
                max-width: 280px;
            }
            
            .steps-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="success-icon">🎉</div>
            <h1 class="header-title">Welcome to ABO-WBO!</h1>
            <p class="header-subtitle">Your account has been created successfully</p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <div class="greeting">Congratulations, {{user_full_name}}!</div>
            
            <div class="welcome-message">
                We're thrilled to welcome you to the ABO-WBO (Aba Oromo Baptist Organization - World Baptist Organization) 
                community. Your registration has been approved and your account is now active. You're now part of our 
                global mission to serve communities and spread the gospel.
            </div>
            
            <!-- Account Information -->
            <div class="account-card">
                <div class="account-header">
                    <div class="account-avatar">
                        {{user_initials}}
                    </div>
                    <div class="account-info">
                        <h3>{{user_full_name}}</h3>
                        <div class="account-role">{{user_position}} - {{user_hierarchy_name}}</div>
                    </div>
                </div>
                
                <div class="account-details">
                    <div class="detail-group">
                        <div class="detail-title">Member ID</div>
                        <div class="detail-value">{{user_member_id}}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-title">Official Email</div>
                        <div class="detail-value">
                            {{internal_email}}
                            <div class="email-badge">ABO-WBO Official</div>
                        </div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-title">Account Created</div>
                        <div class="detail-value">{{account_created_date}}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-title">Account Status</div>
                        <div class="detail-value" style="color: #28a745;">✓ Active</div>
                    </div>
                </div>
            </div>
            
            <!-- Login Credentials -->
            <div class="credentials-section">
                <div class="credentials-header">
                    <div class="credentials-icon">🔐</div>
                    <div class="credentials-title">Your Login Credentials</div>
                </div>
                
                <div class="credentials-grid">
                    <div class="credential-item">
                        <div class="credential-label">Username</div>
                        <div class="credential-value">{{username}}</div>
                    </div>
                    <div class="credential-item">
                        <div class="credential-label">Email Login</div>
                        <div class="credential-value">{{internal_email}}</div>
                    </div>
                    <div class="credential-item">
                        <div class="credential-label">Temporary Password</div>
                        <div class="credential-value">{{temporary_password}}</div>
                    </div>
                </div>
            </div>
            
            <!-- Organizational Placement -->
            <div class="hierarchy-section">
                <div class="hierarchy-title">Your Organizational Placement</div>
                <div class="hierarchy-path">{{full_hierarchy_path}}</div>
                <div class="hierarchy-description">
                    You have been assigned to the {{user_hierarchy_name}} organization as {{user_position}}
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-section">
                <div class="action-title">Get Started</div>
                <div class="action-buttons">
                    <a href="{{login_url}}" class="action-button btn-login">
                        🚀 Login to Dashboard
                    </a>
                    <a href="{{profile_url}}" class="action-button btn-profile">
                        👤 Complete Profile
                    </a>
                    <a href="{{help_url}}" class="action-button btn-help">
                        📚 User Guide
                    </a>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <div class="next-steps-title">Next Steps</div>
                <div class="steps-grid">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-title">Login & Change Password</div>
                        <div class="step-description">Use your temporary password to login and set a secure password</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-title">Complete Your Profile</div>
                        <div class="step-description">Add additional information and upload your profile photo</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-title">Explore Your Dashboard</div>
                        <div class="step-description">Familiarize yourself with your tools and responsibilities</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-title">Connect with Your Team</div>
                        <div class="step-description">Reach out to your colleagues and supervisors</div>
                    </div>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-header">
                    <div class="security-icon">🔒</div>
                    <div class="security-title">Important Security Information</div>
                </div>
                <div class="security-text">
                    <strong>Please change your temporary password immediately after your first login.</strong>
                    Your account security is important to us. Never share your login credentials with anyone, 
                    and always log out from shared computers. If you suspect any unauthorized access to your account, 
                    please contact our IT support team immediately.
                </div>
            </div>
            
            <div class="welcome-message">
                If you have any questions or need assistance getting started, don't hesitate to reach out to your 
                supervisor or our support team. We're here to help you succeed in your role.
            </div>
            
            <div class="welcome-message">
                <strong>Welcome to the ABO-WBO family!</strong> We look forward to working together in service to our communities.
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-welcome">
                Welcome to ABO-WBO - Together in Faith, Service, and Community
            </div>
            
            <div class="footer-text">
                This account creation notification was sent from the ABO-WBO Registration System.
            </div>
            
            <div class="footer-links">
                <a href="{{dashboard_url}}" class="footer-link">Member Dashboard</a>
                <a href="{{directory_url}}" class="footer-link">Member Directory</a>
                <a href="{{resources_url}}" class="footer-link">Resources</a>
                <a href="{{support_url}}" class="footer-link">Support Center</a>
                <a href="{{settings_url}}" class="footer-link">Account Settings</a>
            </div>
            
            <div class="footer-text">
                © {{current_year}} ABO-WBO. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>