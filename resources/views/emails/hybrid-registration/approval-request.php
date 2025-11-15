<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Registration Approval Required - ABO-WBO</title>
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
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            color: #212529;
            padding: 30px;
            text-align: center;
        }
        
        .priority-badge {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .header-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header-subtitle {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        /* Content */
        .email-content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .message {
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        /* Applicant Card */
        .applicant-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #007bff;
        }
        
        .applicant-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .applicant-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-right: 20px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        
        .applicant-info h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .applicant-email {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .applicant-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Hierarchy Display */
        .hierarchy-display {
            background: white;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .hierarchy-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .hierarchy-path {
            font-size: 1.1rem;
            font-weight: 700;
            color: #007bff;
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
            min-width: 140px;
        }
        
        .btn-approve {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            color: white;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: white;
        }
        
        /* Timeline */
        .timeline-section {
            margin: 30px 0;
        }
        
        .timeline-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .timeline-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            font-size: 0.9rem;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-text {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .timeline-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        /* Instructions */
        .instructions {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .instructions-title {
            font-weight: 600;
            color: #0c5460;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .instructions-title::before {
            content: "💡";
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
            color: #0c5460;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        /* Urgency Notice */
        .urgency-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        
        .urgency-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .urgency-text {
            font-weight: 600;
            color: #856404;
            font-size: 1.1rem;
        }
        
        .urgency-subtext {
            color: #856404;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        /* Footer */
        .email-footer {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .footer-text {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .footer-links {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .footer-link {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .footer-link:hover {
            opacity: 1;
            color: #ffc107;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .applicant-header {
                flex-direction: column;
                text-align: center;
            }
            
            .applicant-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .applicant-details {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-button {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="priority-badge">Action Required</div>
            <h1 class="header-title">New Registration Approval</h1>
            <p class="header-subtitle">ABO-WBO Member Registration System</p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <div class="greeting">Dear {{approver_name}},</div>
            
            <div class="message">
                A new member registration has been submitted and requires your approval as a {{approver_position}}. 
                Please review the applicant's information below and take appropriate action.
            </div>
            
            <!-- Applicant Information -->
            <div class="applicant-card">
                <div class="applicant-header">
                    <div class="applicant-avatar">
                        {{applicant_initials}}
                    </div>
                    <div class="applicant-info">
                        <h3>{{applicant_name}}</h3>
                        <div class="applicant-email">{{applicant_email}}</div>
                    </div>
                </div>
                
                <div class="applicant-details">
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">{{applicant_phone}}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Gender:</span>
                        <span class="detail-value">{{applicant_gender}}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Age:</span>
                        <span class="detail-value">{{applicant_age}} years</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Submitted:</span>
                        <span class="detail-value">{{submission_date}}</span>
                    </div>
                </div>
                
                <!-- Requested Hierarchy -->
                <div class="hierarchy-display">
                    <div class="hierarchy-title">Requested Organizational Placement</div>
                    <div class="hierarchy-path">{{target_hierarchy_path}}</div>
                </div>
                
                <!-- Position Request -->
                {{#if target_position}}
                <div class="hierarchy-display">
                    <div class="hierarchy-title">Requested Position</div>
                    <div class="hierarchy-path">{{target_position}}</div>
                </div>
                {{/if}}
            </div>
            
            <!-- Action Buttons -->
            <div class="action-section">
                <div class="action-title">Take Action</div>
                <div class="action-buttons">
                    <a href="{{approve_url}}" class="action-button btn-approve">
                        ✓ Approve Registration
                    </a>
                    <a href="{{reject_url}}" class="action-button btn-reject">
                        ✗ Reject Registration
                    </a>
                    <a href="{{view_url}}" class="action-button btn-view">
                        👁 View Full Details
                    </a>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="timeline-section">
                <div class="timeline-title">Registration Timeline</div>
                <div class="timeline-item">
                    <div class="timeline-icon">1</div>
                    <div class="timeline-content">
                        <div class="timeline-text">Email verified</div>
                        <div class="timeline-time">{{email_verified_at}}</div>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon">2</div>
                    <div class="timeline-content">
                        <div class="timeline-text">Registration form submitted</div>
                        <div class="timeline-time">{{form_submitted_at}}</div>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon">⏳</div>
                    <div class="timeline-content">
                        <div class="timeline-text">Waiting for {{approver_position}} approval</div>
                        <div class="timeline-time">Current step</div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="instructions">
                <div class="instructions-title">Approval Guidelines</div>
                <ul>
                    <li>Review the applicant's information for accuracy and completeness</li>
                    <li>Verify that the requested organizational placement is appropriate</li>
                    <li>Consider the applicant's qualifications for the requested position</li>
                    <li>If approved, the applicant will receive an internal email account</li>
                    <li>If rejected, please provide clear feedback for the applicant</li>
                </ul>
            </div>
            
            <!-- Urgency Notice -->
            <div class="urgency-notice">
                <div class="urgency-icon">⏰</div>
                <div class="urgency-text">Response Required Within {{response_deadline_hours}} Hours</div>
                <div class="urgency-subtext">
                    This request will be automatically escalated if no action is taken by {{escalation_date}}
                </div>
            </div>
            
            <div class="message">
                If you're unable to access the buttons above, you can copy and paste the following links:
                <br><br>
                <strong>Approve:</strong> <a href="{{approve_url}}" style="color: #007bff;">{{approve_url}}</a><br>
                <strong>Reject:</strong> <a href="{{reject_url}}" style="color: #007bff;">{{reject_url}}</a><br>
                <strong>View Details:</strong> <a href="{{view_url}}" style="color: #007bff;">{{view_url}}</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                This approval request was sent from the ABO-WBO Registration System.
            </div>
            
            <div class="footer-links">
                <a href="{{dashboard_url}}" class="footer-link">Registration Dashboard</a>
                <a href="{{help_url}}" class="footer-link">Help & Support</a>
                <a href="{{settings_url}}" class="footer-link">Notification Settings</a>
            </div>
            
            <div class="footer-text">
                © {{current_year}} ABO-WBO. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>