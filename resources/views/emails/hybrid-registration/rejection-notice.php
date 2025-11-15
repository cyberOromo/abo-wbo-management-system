<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Application Update - ABO-WBO</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .status-icon {
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
            font-size: 1.8rem;
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
        
        .message {
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.7;
            color: #495057;
        }
        
        /* Application Summary */
        .application-summary {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border-left: 5px solid #dc3545;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .summary-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .summary-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-right: 20px;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .summary-info h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .summary-status {
            color: #dc3545;
            font-size: 1rem;
            font-weight: 600;
            background: #f8d7da;
            padding: 5px 12px;
            border-radius: 15px;
            display: inline-block;
        }
        
        .summary-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        
        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
        }
        
        /* Rejection Details */
        .rejection-details {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }
        
        .rejection-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .rejection-icon {
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
        
        .rejection-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #721c24;
        }
        
        .rejection-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .rejection-reason {
            margin-bottom: 15px;
        }
        
        .reason-label {
            font-weight: 600;
            color: #721c24;
            margin-bottom: 8px;
        }
        
        .reason-text {
            color: #495057;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
            font-style: italic;
        }
        
        .reviewer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        
        .reviewer-details {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .review-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        /* Next Steps */
        .next-steps {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }
        
        .next-steps-title {
            font-weight: 700;
            color: #0c5460;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
        }
        
        .next-steps-title::before {
            content: "💡";
            margin-right: 12px;
            font-size: 1.3rem;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
        }
        
        .step-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #17a2b8;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: #17a2b8;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            font-size: 0.9rem;
        }
        
        .step-content {
            display: inline-block;
            vertical-align: top;
            width: calc(100% - 50px);
        }
        
        .step-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .step-description {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        /* Reapplication Info */
        .reapplication-info {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        
        .reapplication-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .reapplication-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #856404;
            margin-bottom: 15px;
        }
        
        .reapplication-text {
            color: #856404;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .reapplication-button {
            display: inline-block;
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
            transition: all 0.3s ease;
        }
        
        .reapplication-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            color: #212529;
        }
        
        /* Support Section */
        .support-section {
            background: #e2e3e5;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        
        .support-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .support-text {
            color: #495057;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .support-contacts {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .support-contact {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #495057;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .support-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            color: #007bff;
        }
        
        .contact-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: block;
        }
        
        .contact-text {
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        /* Footer */
        .email-footer {
            background: #2c3e50;
            color: white;
            padding: 40px 30px;
            text-align: center;
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
            color: #ffc107;
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
            
            .summary-header {
                flex-direction: column;
                text-align: center;
            }
            
            .summary-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .summary-details {
                grid-template-columns: 1fr;
            }
            
            .reviewer-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .support-contacts {
                flex-direction: column;
                align-items: center;
            }
            
            .support-contact {
                width: 100%;
                max-width: 200px;
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
            <div class="status-icon">📝</div>
            <h1 class="header-title">Registration Application Update</h1>
            <p class="header-subtitle">ABO-WBO Member Registration System</p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <div class="greeting">Dear {{applicant_name}},</div>
            
            <div class="message">
                Thank you for your interest in joining the ABO-WBO (Aba Oromo Baptist Organization - World Baptist Organization) 
                community. We have carefully reviewed your membership application, and we wanted to provide you with an update 
                on its status.
            </div>
            
            <!-- Application Summary -->
            <div class="application-summary">
                <div class="summary-header">
                    <div class="summary-avatar">
                        {{applicant_initials}}
                    </div>
                    <div class="summary-info">
                        <h3>{{applicant_name}}</h3>
                        <div class="summary-status">Application Requires Revision</div>
                    </div>
                </div>
                
                <div class="summary-details">
                    <div class="detail-item">
                        <div class="detail-label">Application ID</div>
                        <div class="detail-value">{{application_id}}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Submitted</div>
                        <div class="detail-value">{{submission_date}}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Reviewed</div>
                        <div class="detail-value">{{review_date}}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Requested Position</div>
                        <div class="detail-value">{{requested_position}}</div>
                    </div>
                </div>
            </div>
            
            <!-- Rejection Details -->
            <div class="rejection-details">
                <div class="rejection-header">
                    <div class="rejection-icon">ℹ️</div>
                    <div class="rejection-title">Review Feedback</div>
                </div>
                
                <div class="rejection-info">
                    <div class="rejection-reason">
                        <div class="reason-label">Feedback from {{reviewer_name}} ({{reviewer_position}}):</div>
                        <div class="reason-text">{{rejection_reason}}</div>
                    </div>
                    
                    {{#if additional_comments}}
                    <div class="rejection-reason">
                        <div class="reason-label">Additional Comments:</div>
                        <div class="reason-text">{{additional_comments}}</div>
                    </div>
                    {{/if}}
                    
                    <div class="reviewer-info">
                        <div class="reviewer-details">
                            <strong>Reviewed by:</strong> {{reviewer_name}}<br>
                            <strong>Position:</strong> {{reviewer_position}}
                        </div>
                        <div class="review-date">{{review_date_time}}</div>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <div class="next-steps-title">Next Steps</div>
                <ul class="steps-list">
                    <li class="step-item">
                        <span class="step-number">1</span>
                        <div class="step-content">
                            <div class="step-title">Review the Feedback</div>
                            <div class="step-description">
                                Carefully read through the reviewer's comments and understand the areas that need improvement.
                            </div>
                        </div>
                    </li>
                    <li class="step-item">
                        <span class="step-number">2</span>
                        <div class="step-content">
                            <div class="step-title">Address the Concerns</div>
                            <div class="step-description">
                                Make the necessary adjustments to your application based on the feedback provided.
                            </div>
                        </div>
                    </li>
                    <li class="step-item">
                        <span class="step-number">3</span>
                        <div class="step-content">
                            <div class="step-title">Gather Additional Information</div>
                            <div class="step-description">
                                If required, collect any additional documents or information mentioned in the feedback.
                            </div>
                        </div>
                    </li>
                    <li class="step-item">
                        <span class="step-number">4</span>
                        <div class="step-content">
                            <div class="step-title">Resubmit Your Application</div>
                            <div class="step-description">
                                Once you've addressed all concerns, you can resubmit your application for review.
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            
            <!-- Reapplication Information -->
            <div class="reapplication-info">
                <div class="reapplication-icon">🔄</div>
                <div class="reapplication-title">Ready to Reapply?</div>
                <div class="reapplication-text">
                    We encourage you to address the feedback and resubmit your application. Many successful members 
                    have gone through this process, and we're here to support you throughout your journey.
                </div>
                <a href="{{reapplication_url}}" class="reapplication-button">
                    Start New Application
                </a>
            </div>
            
            <!-- Support Section -->
            <div class="support-section">
                <div class="support-title">Need Help or Have Questions?</div>
                <div class="support-text">
                    Our team is here to help you understand the feedback and guide you through the reapplication process. 
                    Don't hesitate to reach out if you need clarification or assistance.
                </div>
                
                <div class="support-contacts">
                    <a href="mailto:{{support_email}}" class="support-contact">
                        <span class="contact-icon">📧</span>
                        <div class="contact-text">Email Support</div>
                    </a>
                    <a href="tel:{{support_phone}}" class="support-contact">
                        <span class="contact-icon">📞</span>
                        <div class="contact-text">Phone Support</div>
                    </a>
                    <a href="{{help_center_url}}" class="support-contact">
                        <span class="contact-icon">💬</span>
                        <div class="contact-text">Help Center</div>
                    </a>
                </div>
            </div>
            
            <div class="message">
                We appreciate your interest in joining ABO-WBO and look forward to potentially welcoming you to our community. 
                Please don't be discouraged by this feedback – it's an opportunity to strengthen your application and better 
                align with our organizational needs.
            </div>
            
            <div class="message">
                <strong>Remember:</strong> This is not a final rejection, but rather guidance to help you submit a stronger 
                application. We believe in the potential of all our applicants and want to see you succeed.
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                This notification was sent from the ABO-WBO Registration System.
            </div>
            
            <div class="footer-links">
                <a href="{{website_url}}" class="footer-link">ABO-WBO Website</a>
                <a href="{{registration_url}}" class="footer-link">Registration Portal</a>
                <a href="{{faq_url}}" class="footer-link">FAQ</a>
                <a href="{{support_url}}" class="footer-link">Support</a>
            </div>
            
            <div class="footer-text">
                © {{current_year}} ABO-WBO. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>