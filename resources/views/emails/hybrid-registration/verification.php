<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABO-WBO Registration Verification</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .org-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .org-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
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
        
        .message {
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        /* Verification Code */
        .verification-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            border: 2px solid #e9ecef;
        }
        
        .verification-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .verification-code {
            font-size: 2.5rem;
            font-weight: 700;
            color: #007bff;
            letter-spacing: 0.5rem;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 3px solid #007bff;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
        }
        
        .verification-note {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 15px;
        }
        
        /* Action Button */
        .action-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: transform 0.3s ease;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
        
        /* Instructions */
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .instructions-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .instructions-title::before {
            content: "ℹ️";
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        /* Security Notice */
        .security-notice {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .security-title {
            font-weight: 600;
            color: #721c24;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .security-title::before {
            content: "🔒";
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .security-text {
            color: #721c24;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        /* Footer */
        .email-footer {
            background: #f8f9fa;
            padding: 30px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        
        .footer-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .contact-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-link {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 50%;
            margin: 0 5px;
            line-height: 40px;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        
        .social-link:hover {
            background: #007bff;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .verification-code {
                font-size: 2rem;
                letter-spacing: 0.3rem;
                padding: 15px;
            }
            
            .org-name {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">ABO</div>
            <h1 class="org-name">ABO-WBO</h1>
            <p class="org-subtitle">Afaan Oromoo Business & Workers Organization</p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <div class="greeting">Welcome to ABO-WBO!</div>
            
            <div class="message">
                Thank you for starting your registration with the Afaan Oromoo Business & Workers Organization. 
                To complete your registration process, please verify your email address using the verification code below.
            </div>
            
            <!-- Verification Code Section -->
            <div class="verification-section">
                <div class="verification-label">Your Verification Code</div>
                <div class="verification-code">{{verification_code}}</div>
                <div class="verification-note">
                    <strong>This code expires in {{expires_hours}} hours</strong>
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="action-section">
                <a href="{{verification_url}}" class="action-button">
                    Continue Registration
                </a>
            </div>
            
            <!-- Instructions -->
            <div class="instructions">
                <div class="instructions-title">How to use this code:</div>
                <ul>
                    <li>Return to the registration page where you entered your email</li>
                    <li>Enter the 6-digit verification code exactly as shown above</li>
                    <li>Click "Verify Email" to continue with your registration</li>
                    <li>Complete your profile information and organizational placement</li>
                </ul>
            </div>
            
            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-title">Security Notice</div>
                <div class="security-text">
                    This verification code is confidential and should not be shared with anyone. 
                    If you did not request this registration, please ignore this email or contact our support team immediately.
                </div>
            </div>
            
            <div class="message">
                If you're having trouble with the button above, you can copy and paste the following link into your browser:
                <br><br>
                <a href="{{verification_url}}" style="color: #007bff; word-break: break-all;">{{verification_url}}</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                This email was sent from ABO-WBO Registration System. 
                If you have any questions, please contact our support team.
            </div>
            
            <div class="contact-info">
                📧 support@abo-wbo.org | 📞 +251-XXX-XXX-XXX | 🌐 www.abo-wbo.org
            </div>
            
            <div class="footer-text">
                © {{current_year}} ABO-WBO. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>