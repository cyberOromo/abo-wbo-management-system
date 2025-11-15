# ABO-WBO Management System API Documentation

## Overview

The ABO-WBO Management System provides a comprehensive RESTful API for managing organizational operations across a 4-tier hierarchy (Global → Godina → Gamta → Gurmu).

**Base URL:** `https://your-domain.com/api/v1`  
**Authentication:** Bearer Token (JWT)  
**Content-Type:** `application/json`  
**Response Format:** JSON

---

## Authentication

### Register New User
```http
POST /api/auth/register
```

**Request Body:**
```json
{
    "first_name": "string (required)",
    "last_name": "string (required)",
    "email": "string (required, unique)",
    "phone": "string (optional)",
    "password": "string (required, min:8)",
    "password_confirmation": "string (required)",
    "gurmu_id": "integer (required)",
    "position_id": "integer (optional)",
    "language_preference": "string (en|om, default: en)"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful. Please check your email for verification.",
    "data": {
        "user": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "email": "user@example.com",
            "status": "pending"
        }
    }
}
```

### Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "string (required)",
    "password": "string (required)",
    "remember_me": "boolean (optional)"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "email": "user@example.com",
            "full_name": "John Doe",
            "level_scope": "gurmu",
            "position": "Secretary",
            "gurmu": {
                "id": 1,
                "name": "Gurmu Minneapolis",
                "gamta": "Minnesota",
                "godina": "USA"
            }
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expires_at": "2025-11-25T10:00:00Z"
    }
}
```

### Logout
```http
POST /api/auth/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## User Management

### Get Users List
```http
GET /api/users
```

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 20, max: 100)
- `search` (string): Search in name, email
- `status` (string): pending|active|suspended|inactive
- `level_scope` (string): global|godina|gamta|gurmu
- `gurmu_id` (integer): Filter by Gurmu
- `gamta_id` (integer): Filter by Gamta
- `godina_id` (integer): Filter by Godina
- `position_id` (integer): Filter by position
- `sort_by` (string): created_at|last_login|name (default: created_at)
- `sort_order` (string): asc|desc (default: desc)

**Response:**
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "full_name": "John Doe",
                "email": "john@example.com",
                "phone": "+1234567890",
                "status": "active",
                "level_scope": "gurmu",
                "position": {
                    "id": 2,
                    "name_en": "Secretary",
                    "name_om": "Barreessaa"
                },
                "hierarchy": {
                    "gurmu": "Gurmu Minneapolis",
                    "gamta": "Minnesota",
                    "godina": "USA"
                },
                "last_login": "2025-10-26T10:30:00Z",
                "created_at": "2025-01-15T09:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 150,
            "total_pages": 8,
            "has_next": true,
            "has_prev": false
        }
    }
}
```

### Get User Details
```http
GET /api/users/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "date_of_birth": "1990-05-15",
            "gender": "male",
            "address": "123 Main St, Minneapolis, MN",
            "status": "active",
            "level_scope": "gurmu",
            "language_preference": "en",
            "position": {
                "id": 2,
                "name_en": "Secretary",
                "name_om": "Barreessaa"
            },
            "hierarchy": {
                "gurmu": {
                    "id": 1,
                    "name": "Gurmu Minneapolis",
                    "code": "MN-MPLS"
                },
                "gamta": {
                    "id": 1,
                    "name": "Minnesota",
                    "code": "USA-MN"
                },
                "godina": {
                    "id": 1,
                    "name": "USA",
                    "code": "USA"
                }
            },
            "statistics": {
                "tasks_assigned": 15,
                "tasks_completed": 12,
                "meetings_attended": 8,
                "donations_made": 5,
                "courses_completed": 3
            },
            "created_at": "2025-01-15T09:00:00Z",
            "last_login": "2025-10-26T10:30:00Z"
        }
    }
}
```

### Create User (Admin Only)
```http
POST /api/users
```

**Request Body:**
```json
{
    "first_name": "string (required)",
    "last_name": "string (required)",
    "email": "string (required, unique)",
    "phone": "string (optional)",
    "password": "string (required, min:8)",
    "gurmu_id": "integer (required)",
    "position_id": "integer (optional)",
    "level_scope": "string (required: global|godina|gamta|gurmu)",
    "language_preference": "string (en|om, default: en)",
    "status": "string (pending|active, default: pending)"
}
```

### Update User
```http
PUT /api/users/{id}
```

**Request Body:**
```json
{
    "first_name": "string (optional)",
    "last_name": "string (optional)",
    "phone": "string (optional)",
    "date_of_birth": "string (YYYY-MM-DD, optional)",
    "gender": "string (male|female|other|prefer_not_to_say, optional)",
    "address": "string (optional)",
    "language_preference": "string (en|om, optional)",
    "notification_preferences": {
        "email": true,
        "sms": false,
        "push": true
    }
}
```

### Approve User Registration
```http
PUT /api/users/{id}/approve
```

**Request Body:**
```json
{
    "position_id": "integer (optional)",
    "approval_note": "string (optional)"
}
```

### Suspend User
```http
PUT /api/users/{id}/suspend
```

**Request Body:**
```json
{
    "reason": "string (required)",
    "duration_days": "integer (optional, null for indefinite)"
}
```

---

## Hierarchy Management

### Get Godinas
```http
GET /api/hierarchy/godinas
```

**Response:**
```json
{
    "success": true,
    "data": {
        "godinas": [
            {
                "id": 1,
                "name": "USA",
                "code": "USA",
                "description": "United States Regional Organization",
                "status": "active",
                "gamtas_count": 5,
                "gurmus_count": 12,
                "members_count": 150
            }
        ]
    }
}
```

### Get Gamtas by Godina
```http
GET /api/hierarchy/godinas/{godina_id}/gamtas
```

### Get Gurmus by Gamta
```http
GET /api/hierarchy/gamtas/{gamta_id}/gurmus
```

### Create Hierarchy (Admin Only)
```http
POST /api/hierarchy/godinas
POST /api/hierarchy/gamtas
POST /api/hierarchy/gurmus
```

---

## Task Management

### Get Tasks
```http
GET /api/tasks
```

**Query Parameters:**
- `page`, `per_page`, `search`
- `status` (string): pending|in_progress|under_review|completed|cancelled|on_hold|blocked
- `priority` (string): low|medium|high|urgent|critical
- `category` (string): administrative|financial|educational|social|technical|communication|planning
- `assigned_to_me` (boolean): Filter tasks assigned to current user
- `created_by_me` (boolean): Filter tasks created by current user
- `level_scope` (string): global|godina|gamta|gurmu|cross_level
- `due_date_from`, `due_date_to` (string): Date range filter (YYYY-MM-DD)
- `event_id`, `meeting_id` (integer): Filter by related entity

**Response:**
```json
{
    "success": true,
    "data": {
        "tasks": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "title": "Prepare Annual Budget Report",
                "description": "Create comprehensive budget report for 2025",
                "status": "in_progress",
                "priority": "high",
                "category": "financial",
                "level_scope": "gurmu",
                "completion_percentage": 75,
                "due_date": "2025-11-15",
                "estimated_hours": 20,
                "actual_hours": 15,
                "created_by": {
                    "id": 1,
                    "name": "John Doe"
                },
                "assigned_to": [
                    {
                        "id": 2,
                        "name": "Jane Smith",
                        "type": "user"
                    }
                ],
                "tags": ["budget", "financial", "annual"],
                "attachments_count": 3,
                "comments_count": 5,
                "created_at": "2025-10-01T09:00:00Z",
                "updated_at": "2025-10-25T14:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 45,
            "total_pages": 3
        }
    }
}
```

### Create Task
```http
POST /api/tasks
```

**Request Body:**
```json
{
    "title": "string (required)",
    "description": "string (optional)",
    "level_scope": "string (required: global|godina|gamta|gurmu|cross_level)",
    "scope_id": "integer (optional)",
    "category": "string (optional: administrative|financial|educational|social|technical|communication|planning)",
    "priority": "string (optional: low|medium|high|urgent|critical)",
    "due_date": "string (optional, YYYY-MM-DD)",
    "estimated_hours": "integer (optional)",
    "assigned_to": [
        {
            "type": "user",
            "id": 2
        }
    ],
    "tags": ["string"],
    "parent_task_id": "integer (optional)",
    "event_id": "integer (optional)",
    "meeting_id": "integer (optional)"
}
```

### Update Task
```http
PUT /api/tasks/{id}
```

### Update Task Status
```http
PUT /api/tasks/{id}/status
```

**Request Body:**
```json
{
    "status": "string (required: pending|in_progress|under_review|completed|cancelled|on_hold|blocked)",
    "completion_percentage": "integer (optional, 0-100)",
    "actual_hours": "integer (optional)",
    "note": "string (optional)"
}
```

### Assign Task
```http
POST /api/tasks/{id}/assign
```

**Request Body:**
```json
{
    "assigned_to": [
        {
            "type": "user",
            "id": 2
        }
    ],
    "note": "string (optional)"
}
```

### Add Task Comment
```http
POST /api/tasks/{id}/comments
```

**Request Body:**
```json
{
    "comment": "string (required)",
    "is_internal": "boolean (optional, default: false)",
    "attachments": ["file_uuid1", "file_uuid2"]
}
```

---

## Meeting Management

### Get Meetings
```http
GET /api/meetings
```

**Query Parameters:**
- `page`, `per_page`, `search`
- `meeting_type` (string): regular|emergency|special|training|board|committee|general
- `status` (string): scheduled|ongoing|completed|cancelled|postponed
- `level_scope` (string): global|godina|gamta|gurmu|cross_level
- `date_from`, `date_to` (string): Date range filter (YYYY-MM-DD)
- `is_virtual` (boolean): Filter virtual meetings
- `organized_by_me` (boolean): Filter meetings organized by current user
- `attending` (boolean): Filter meetings user is attending

**Response:**
```json
{
    "success": true,
    "data": {
        "meetings": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "title": "Monthly Board Meeting",
                "description": "Regular monthly board meeting",
                "meeting_type": "board",
                "level_scope": "gurmu",
                "start_datetime": "2025-11-01T10:00:00Z",
                "end_datetime": "2025-11-01T12:00:00Z",
                "timezone": "America/Chicago",
                "is_virtual": true,
                "zoom_join_url": "https://zoom.us/j/123456789",
                "status": "scheduled",
                "organizer": {
                    "id": 1,
                    "name": "John Doe"
                },
                "attendees_count": 12,
                "my_status": "accepted",
                "has_agenda": true,
                "has_recording": false,
                "created_at": "2025-10-15T09:00:00Z"
            }
        ]
    }
}
```

### Create Meeting
```http
POST /api/meetings
```

**Request Body:**
```json
{
    "title": "string (required)",
    "description": "string (optional)",
    "meeting_type": "string (required: regular|emergency|special|training|board|committee|general)",
    "level_scope": "string (required: global|godina|gamta|gurmu|cross_level)",
    "scope_id": "integer (optional)",
    "start_datetime": "string (required, ISO 8601)",
    "end_datetime": "string (required, ISO 8601)",
    "timezone": "string (required)",
    "is_virtual": "boolean (default: true)",
    "location": "string (optional, required if not virtual)",
    "agenda": "string (optional)",
    "attendees": [
        {
            "user_id": 2,
            "role": "attendee"
        }
    ],
    "registration_required": "boolean (default: false)",
    "max_attendees": "integer (optional)"
}
```

### Join Meeting
```http
POST /api/meetings/{id}/join
```

**Response:**
```json
{
    "success": true,
    "data": {
        "join_url": "https://zoom.us/j/123456789?pwd=abc123",
        "meeting_id": "123456789",
        "password": "abc123",
        "dial_in_numbers": [
            "+1 312 626 6799"
        ]
    }
}
```

### Save Meeting Minutes
```http
POST /api/meetings/{id}/minutes
```

**Request Body:**
```json
{
    "minutes": "string (required)",
    "action_items": [
        {
            "description": "Follow up on budget approval",
            "assigned_to": 2,
            "due_date": "2025-11-15"
        }
    ],
    "attachments": ["file_uuid1", "file_uuid2"]
}
```

---

## Donation Management

### Get Donations
```http
GET /api/donations
```

**Query Parameters:**
- `page`, `per_page`, `search`
- `donor_type` (string): member|anonymous|organization|corporate
- `donation_type` (string): general|membership_fee|event|project|pledge|emergency|scholarship
- `payment_status` (string): pending|processing|completed|failed|refunded|cancelled
- `payment_method` (string): paypal|stripe|bank_transfer|cash|check|manual
- `amount_min`, `amount_max` (decimal): Amount range filter
- `currency` (string): USD|EUR|CAD|etc.
- `date_from`, `date_to` (string): Date range filter
- `gurmu_id`, `gamta_id`, `godina_id` (integer): Hierarchy filters

**Response:**
```json
{
    "success": true,
    "data": {
        "donations": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "donor_name": "John Doe",
                "donor_type": "member",
                "amount": 100.00,
                "currency": "USD",
                "donation_type": "membership_fee",
                "payment_method": "paypal",
                "payment_status": "completed",
                "receipt_number": "RCP-2025-001",
                "receipt_generated": true,
                "gurmu": {
                    "id": 1,
                    "name": "Gurmu Minneapolis"
                },
                "created_at": "2025-10-25T14:30:00Z"
            }
        ],
        "summary": {
            "total_amount": 2500.00,
            "total_count": 25,
            "currency_breakdown": {
                "USD": 2000.00,
                "EUR": 300.00,
                "CAD": 200.00
            }
        }
    }
}
```

### Create Donation
```http
POST /api/donations
```

**Request Body:**
```json
{
    "donor_type": "string (required: member|anonymous|organization|corporate)",
    "donor_name": "string (required if not member)",
    "donor_email": "string (optional)",
    "donor_phone": "string (optional)",
    "member_id": "integer (required if donor_type is member)",
    "amount": "decimal (required)",
    "currency": "string (required, default: USD)",
    "donation_type": "string (required: general|membership_fee|event|project|pledge|emergency|scholarship)",
    "payment_method": "string (required: paypal|stripe|bank_transfer|cash|check|manual)",
    "event_id": "integer (optional)",
    "project_id": "integer (optional)",
    "gurmu_id": "integer (required)",
    "is_anonymous": "boolean (default: false)",
    "notes": "string (optional)"
}
```

### Process Payment
```http
POST /api/donations/{id}/process-payment
```

**Request Body:**
```json
{
    "payment_method": "string (required: paypal|stripe)",
    "payment_token": "string (required)",
    "return_url": "string (optional)",
    "cancel_url": "string (optional)"
}
```

### Generate Receipt
```http
GET /api/donations/{id}/receipt
```

**Response:** PDF file download

### Approve Donation (Admin/Finance)
```http
POST /api/donations/{id}/approve
```

**Request Body:**
```json
{
    "notes": "string (optional)"
}
```

---

## Event Management

### Get Events
```http
GET /api/events
```

**Query Parameters:**
- `page`, `per_page`, `search`
- `event_type` (string): cultural|educational|fundraising|social|political|religious|sports|conference
- `status` (string): draft|published|ongoing|completed|cancelled|postponed
- `level_scope` (string): global|godina|gamta|gurmu|cross_level
- `date_from`, `date_to` (string): Date range filter
- `is_virtual` (boolean): Filter virtual events
- `is_paid_event` (boolean): Filter paid events
- `organized_by_me` (boolean): Filter events organized by current user
- `registered` (boolean): Filter events user is registered for

**Response:**
```json
{
    "success": true,
    "data": {
        "events": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "title": "Annual Cultural Festival",
                "description": "Celebrating Oromo heritage and culture",
                "event_type": "cultural",
                "level_scope": "godina",
                "start_datetime": "2025-12-15T18:00:00Z",
                "end_datetime": "2025-12-15T23:00:00Z",
                "venue_name": "Community Center",
                "venue_address": "123 Festival Ave, Minneapolis, MN",
                "is_virtual": false,
                "is_paid_event": true,
                "ticket_price": 25.00,
                "currency": "USD",
                "max_attendees": 200,
                "registered_count": 85,
                "status": "published",
                "organizer": {
                    "id": 1,
                    "name": "John Doe"
                },
                "my_registration_status": "registered",
                "featured_image": "https://example.com/event-image.jpg",
                "created_at": "2025-10-01T09:00:00Z"
            }
        ]
    }
}
```

### Create Event
```http
POST /api/events
```

**Request Body:**
```json
{
    "title": "string (required)",
    "description": "string (optional)",
    "event_type": "string (required: cultural|educational|fundraising|social|political|religious|sports|conference)",
    "level_scope": "string (required: global|godina|gamta|gurmu|cross_level)",
    "scope_id": "integer (optional)",
    "start_datetime": "string (required, ISO 8601)",
    "end_datetime": "string (required, ISO 8601)",
    "timezone": "string (required)",
    "venue_name": "string (optional)",
    "venue_address": "string (optional)",
    "is_virtual": "boolean (default: false)",
    "virtual_platform": "string (optional)",
    "virtual_link": "string (optional)",
    "is_paid_event": "boolean (default: false)",
    "ticket_price": "decimal (optional)",
    "currency": "string (default: USD)",
    "max_attendees": "integer (optional)",
    "registration_required": "boolean (default: true)",
    "registration_deadline": "string (optional, ISO 8601)",
    "agenda": [
        {
            "time": "18:00",
            "title": "Welcome & Opening",
            "description": "Welcome remarks by organizers"
        }
    ],
    "requirements": "string (optional)"
}
```

### Register for Event
```http
POST /api/events/{id}/register
```

**Request Body:**
```json
{
    "registration_type": "string (optional: participant|volunteer|speaker|sponsor|organizer)",
    "special_requirements": "string (optional)",
    "dietary_preferences": ["vegetarian", "halal"],
    "emergency_contact": {
        "name": "Jane Doe",
        "phone": "+1234567890",
        "relationship": "spouse"
    }
}
```

---

## Course Management (Education System)

### Get Courses
```http
GET /api/courses
```

**Query Parameters:**
- `page`, `per_page`, `search`
- `category_id` (integer): Filter by category
- `level_scope` (string): global|godina|gamta|gurmu|all
- `difficulty_level` (string): beginner|intermediate|advanced|expert
- `status` (string): draft|published|archived|suspended
- `is_free` (boolean): Filter free/paid courses
- `enrolled` (boolean): Filter courses user is enrolled in
- `required_for_me` (boolean): Filter courses required for user's position

**Response:**
```json
{
    "success": true,
    "data": {
        "courses": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "title_en": "Leadership Fundamentals",
                "title_om": "Bu'uuraalee Hooggansummaa",
                "short_description_en": "Learn the basics of effective leadership",
                "category": {
                    "id": 1,
                    "name_en": "Leadership Development"
                },
                "difficulty_level": "beginner",
                "duration_hours": 20,
                "lessons_count": 8,
                "enrolled_count": 45,
                "is_free": true,
                "my_enrollment": {
                    "status": "in_progress",
                    "progress_percentage": 60,
                    "current_lesson": 5
                },
                "featured_image": "https://example.com/course-image.jpg",
                "created_at": "2025-09-01T09:00:00Z"
            }
        ]
    }
}
```

### Enroll in Course
```http
POST /api/courses/{id}/enroll
```

### Get Course Progress
```http
GET /api/courses/{id}/progress
```

**Response:**
```json
{
    "success": true,
    "data": {
        "enrollment": {
            "id": 1,
            "status": "in_progress",
            "progress_percentage": 60,
            "lessons_completed": 5,
            "lessons_total": 8,
            "time_spent_minutes": 180,
            "current_lesson": {
                "id": 6,
                "title_en": "Communication Skills"
            },
            "start_date": "2025-10-01T09:00:00Z"
        },
        "lessons": [
            {
                "id": 1,
                "title_en": "Introduction to Leadership",
                "status": "completed",
                "progress_percentage": 100,
                "completed_at": "2025-10-02T14:30:00Z"
            }
        ]
    }
}
```

### Update Lesson Progress
```http
PUT /api/lessons/{id}/progress
```

**Request Body:**
```json
{
    "status": "string (in_progress|completed)",
    "progress_percentage": "integer (0-100)",
    "time_spent_minutes": "integer",
    "quiz_responses": {},
    "notes": "string (optional)"
}
```

---

## Notification Management

### Get Notifications
```http
GET /api/notifications
```

**Query Parameters:**
- `page`, `per_page`
- `status` (string): pending|sent|delivered|read|failed
- `category` (string): task|meeting|donation|event|course|system|announcement|approval
- `priority` (string): low|medium|high|urgent
- `unread_only` (boolean): Filter unread notifications

**Response:**
```json
{
    "success": true,
    "data": {
        "notifications": [
            {
                "id": 1,
                "uuid": "550e8400-e29b-41d4-a716-446655440000",
                "title": "New Task Assigned",
                "message": "You have been assigned a new task: Prepare Budget Report",
                "type": "info",
                "category": "task",
                "priority": "medium",
                "status": "read",
                "action_url": "/tasks/1",
                "action_text": "View Task",
                "sender": {
                    "id": 1,
                    "name": "John Doe"
                },
                "created_at": "2025-10-25T10:00:00Z",
                "read_at": "2025-10-25T10:05:00Z"
            }
        ],
        "unread_count": 5
    }
}
```

### Mark Notification as Read
```http
PUT /api/notifications/{id}/read
```

### Mark All Notifications as Read
```http
PUT /api/notifications/mark-all-read
```

---

## Reporting & Analytics

### Get Dashboard Statistics
```http
GET /api/reports/dashboard
```

**Query Parameters:**
- `date_from`, `date_to` (string): Date range filter
- `level_scope` (string): Filter by hierarchy level

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_members": 150,
            "active_members": 135,
            "pending_approvals": 5,
            "total_donations": 25000.00,
            "monthly_donations": 2500.00,
            "active_tasks": 45,
            "completed_tasks": 120,
            "upcoming_meetings": 8,
            "active_events": 3
        },
        "charts": {
            "member_growth": [
                {"month": "2025-01", "count": 100},
                {"month": "2025-02", "count": 110}
            ],
            "donation_trends": [
                {"month": "2025-01", "amount": 2000.00},
                {"month": "2025-02", "amount": 2200.00}
            ],
            "task_completion": {
                "completed": 75,
                "in_progress": 20,
                "pending": 15
            }
        },
        "recent_activities": [
            {
                "type": "task_completed",
                "description": "Budget Report task completed by John Doe",
                "timestamp": "2025-10-25T14:30:00Z"
            }
        ]
    }
}
```

### Generate Custom Report
```http
POST /api/reports/custom
```

**Request Body:**
```json
{
    "report_type": "string (required: members|donations|tasks|meetings|events|courses)",
    "date_from": "string (required, YYYY-MM-DD)",
    "date_to": "string (required, YYYY-MM-DD)",
    "filters": {
        "level_scope": "gurmu",
        "status": "active",
        "gurmu_id": 1
    },
    "format": "string (json|csv|pdf, default: json)",
    "include_charts": "boolean (default: false)"
}
```

### Export Data
```http
GET /api/reports/{report_type}/export
```

**Query Parameters:**
- `format` (string): csv|excel|pdf
- `date_from`, `date_to` (string): Date range
- Additional filters based on report type

**Response:** File download

---

## File Upload & Management

### Upload File
```http
POST /api/files/upload
```

**Request:** `multipart/form-data`
- `file` (file): The file to upload
- `upload_type` (string): profile_image|document|receipt|meeting_recording|course_material|event_image|general
- `related_entity_type` (string, optional): task|meeting|event|course|user
- `related_entity_id` (integer, optional): ID of related entity
- `is_public` (boolean, default: false): Whether file is publicly accessible

**Response:**
```json
{
    "success": true,
    "data": {
        "file": {
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "original_name": "document.pdf",
            "file_size": 1024000,
            "mime_type": "application/pdf",
            "upload_type": "document",
            "url": "https://example.com/files/550e8400-e29b-41d4-a716-446655440000",
            "download_url": "https://example.com/api/files/550e8400-e29b-41d4-a716-446655440000/download"
        }
    }
}
```

### Download File
```http
GET /api/files/{uuid}/download
```

### Delete File
```http
DELETE /api/files/{uuid}
```

---

## Error Responses

All API endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    },
    "error_code": "VALIDATION_ERROR",
    "status_code": 422
}
```

### Common HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (Invalid/missing token)
- `403` - Forbidden (Insufficient permissions)
- `404` - Not Found
- `422` - Unprocessable Entity (Validation errors)
- `429` - Too Many Requests (Rate limiting)
- `500` - Internal Server Error

### Error Codes

- `VALIDATION_ERROR` - Input validation failed
- `AUTHENTICATION_REQUIRED` - Valid authentication token required
- `INSUFFICIENT_PERMISSIONS` - User lacks required permissions
- `RESOURCE_NOT_FOUND` - Requested resource doesn't exist
- `DUPLICATE_ENTRY` - Resource already exists
- `RATE_LIMIT_EXCEEDED` - Too many requests
- `PAYMENT_FAILED` - Payment processing failed
- `FILE_UPLOAD_FAILED` - File upload error
- `EXTERNAL_SERVICE_ERROR` - Third-party service error

---

## Rate Limiting

API requests are rate limited per user:

- **Authenticated users**: 1000 requests per hour
- **File uploads**: 50 uploads per hour
- **Authentication endpoints**: 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1635724800
```

---

## Webhooks

The system supports webhooks for real-time notifications:

### Webhook Events
- `user.registered` - New user registration
- `user.approved` - User registration approved
- `task.created` - New task created
- `task.completed` - Task completed
- `meeting.scheduled` - Meeting scheduled
- `donation.received` - Donation received
- `event.published` - Event published

### Webhook Payload Example
```json
{
    "event": "task.completed",
    "timestamp": "2025-10-25T14:30:00Z",
    "data": {
        "task": {
            "id": 1,
            "title": "Budget Report",
            "completed_by": {
                "id": 2,
                "name": "Jane Smith"
            }
        }
    }
}
```

---

## SDK & Libraries

Official SDKs available for:
- **JavaScript/Node.js**: `npm install abo-wbo-api-client`
- **PHP**: `composer require abo-wbo/api-client`
- **Python**: `pip install abo-wbo-api-client`

### JavaScript Example
```javascript
import { AboWboClient } from 'abo-wbo-api-client';

const client = new AboWboClient({
    baseUrl: 'https://your-domain.com/api/v1',
    token: 'your-jwt-token'
});

const tasks = await client.tasks.list({
    status: 'in_progress',
    assigned_to_me: true
});
```

---

## Postman Collection

Download the complete Postman collection:
[ABO-WBO API Collection](https://example.com/postman-collection.json)

---

## Support

- **API Documentation**: [https://docs.abo-wbo.org/api](https://docs.abo-wbo.org/api)
- **Status Page**: [https://status.abo-wbo.org](https://status.abo-wbo.org)
- **Support Email**: api-support@abo-wbo.org