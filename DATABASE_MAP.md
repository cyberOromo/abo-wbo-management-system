# Database Map

## Canonical Source

The most reliable schema source is `database/schema.sql`. The repository contains many additional SQL and migration artifacts, but they should be treated as secondary until reconciled with the canonical file and the live database.

## Table Inventory

### Core Organization

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `globals` | `id` | top-level organizational scope |
| `godinas` | `id` | top-tier subdivisions under global |
| `gamtas` | `id` | subdivisions under godinas |
| `gurmus` | `id` | local group or membership unit under gamtas |

### Positions and Responsibilities

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `positions` | `id` | organizational positions by scope |
| `responsibilities` | `id` | general responsibility definitions |
| `responsibility_assignments` | `id` | user-to-responsibility assignment records |
| `individual_responsibilities` | `id` | fixed position-key responsibility set |
| `shared_responsibilities` | `id` | fixed level-based shared responsibilities |
| `roles` | `id` | technical/system roles |

### Users and Access

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `users` | `id` | core user account table |
| `user_roles` | `id` | optional many-to-many technical role mapping |
| `user_sessions` | `id` | persisted session records |
| `password_reset_tokens` | `id` | password reset tokens |
| `email_verification_tokens` | `id` | email verification tokens |
| `user_assignments` | `id` | executive/position assignment workflow |

### Tasks and Meetings

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `tasks` | `id` | work items |
| `task_comments` | `id` | task discussion/comments |
| `task_activities` | `id` | task audit/activity stream |
| `meetings` | `id` | meeting definitions |
| `meeting_attendees` | `id` | participant attendance |
| `meeting_activities` | `id` | meeting activity log |

### Donations and Events

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `donors` | `id` | donor entities |
| `donation_campaigns` | `id` | fundraising campaigns |
| `donations` | `id` | donation transactions |
| `events` | `id` | events |
| `event_registrations` | `id` | event registration records |
| `event_participants` | `id` | alternative participant tracking |

### Courses and Notifications

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `course_categories` | `id` | course categorization |
| `courses` | `id` | course definitions |
| `lessons` | `id` | lessons under courses |
| `course_enrollments` | `id` | user course enrollment |
| `lesson_progress` | `id` | lesson-level progress |
| `notifications` | `id` | in-app notification records |

### Audit and Utility

| Table | Primary Key | Purpose |
| --- | --- | --- |
| `audit_logs` | `id` | user action auditing |
| `system_logs` | `id` | system log persistence |
| `file_uploads` | `id` | uploaded file metadata |
| `system_settings` | `id` | key-value settings |

## Relationship Map

### Organization Hierarchy

- `godinas.global_id -> globals.id`
- `gamtas.godina_id -> godinas.id`
- `gurmus.gamta_id -> gamtas.id`
- `users.gurmu_id -> gurmus.id`

This is the backbone of most scope-aware behavior.

### User and Role Relationships

- `users.approved_by -> users.id`
- `user_roles.user_id -> users.id`
- `user_roles.role_id -> roles.id`
- `user_roles.assigned_by -> users.id`
- `user_sessions.user_id -> users.id`
- `password_reset_tokens.user_id -> users.id`
- `email_verification_tokens.user_id -> users.id`

### Assignment and Responsibility Relationships

- `user_assignments.user_id -> users.id`
- `user_assignments.position_id -> positions.id`
- `user_assignments.assigned_by -> users.id`
- `user_assignments.approved_by -> users.id`
- `user_assignments.ended_by -> users.id`
- `responsibility_assignments.user_id -> users.id`
- `responsibility_assignments.responsibility_id -> responsibilities.id`
- `responsibility_assignments.position_id -> positions.id`
- `responsibility_assignments.assigned_by -> users.id`
- `responsibility_assignments.approved_by -> users.id`

Note: `organizational_unit_id` in both `user_assignments` and `responsibility_assignments` is polymorphic by convention and is not enforced by a foreign key. Integrity depends on `level_scope` and application logic.

### Tasks and Meetings

- `tasks.created_by -> users.id`
- `tasks.parent_task_id -> tasks.id`
- `task_comments.task_id -> tasks.id`
- `task_comments.user_id -> users.id`
- `task_comments.parent_comment_id -> task_comments.id`
- `task_activities.task_id -> tasks.id`
- `task_activities.user_id -> users.id`
- `meetings.organized_by -> users.id`
- `meeting_attendees.meeting_id -> meetings.id`
- `meeting_attendees.user_id -> users.id`
- `meeting_activities.meeting_id -> meetings.id`
- `meeting_activities.user_id -> users.id`

### Events

- `events.organized_by -> users.id`
- `event_registrations.event_id -> events.id`
- `event_registrations.user_id -> users.id`
- `event_participants.event_id -> events.id`
- `event_participants.user_id -> users.id`

### Courses

- `course_categories.parent_category_id -> course_categories.id`
- `courses.category_id -> course_categories.id`
- `courses.instructor_id -> users.id`
- `lessons.course_id -> courses.id`
- `course_enrollments.course_id -> courses.id`
- `course_enrollments.user_id -> users.id`
- `lesson_progress.enrollment_id -> course_enrollments.id`
- `lesson_progress.lesson_id -> lessons.id`

### Donations and Notifications

- `donation_campaigns.global_id -> globals.id`
- `notifications.recipient_id -> users.id`
- `notifications.sender_id -> users.id`
- `audit_logs.user_id -> users.id`
- `file_uploads.user_id -> users.id`

## Schema Characteristics

### Strengths

- broad coverage for the main business modules
- mostly explicit foreign keys for user-centric relations
- useful indexing on status, scope, and timestamps
- full-text indexes on some search-oriented tables
- JSON columns used for extensibility without immediate schema churn

### High-Risk Schema Issues

1. Polymorphic organizational scope fields are not referentially enforced.
   - `organizational_unit_id` depends on `level_scope` and can drift from actual hierarchy rows.

2. JSON is used where relational modeling might be expected.
   - Examples include `tasks.assigned_to`, `tasks.target_audience`, `notifications.channels`, `events.speakers`, `courses.prerequisites`.
   - This reduces queryability and can produce duplicated application logic.

3. Duplicate event participation models exist.
   - `event_registrations` and `event_participants` overlap in purpose.
   - This can cause ambiguity in reporting and attendance logic.

4. Some tables are clearly richer than the queries currently hitting them.
   - Live validation showed a report query expecting `meetings.start_date`, while the schema uses `start_datetime` and `end_datetime`.

5. Donor and donation modeling appears split between donor master data and direct donation records.
   - This can be valid, but application usage must be checked before assuming both are fully active.

## Missing or Weakly Enforced Areas

### Likely Integrity Gaps

- no FK enforcement for polymorphic organization assignments
- no FK from `individual_responsibilities.position_key` to `positions.key_name`
- no FK from `donors.created_by` visible in the canonical snippet
- no generic integrity guardrails around JSON-based foreign-key-like arrays

### Needs Clarification

- whether all route-level modules actually use the full canonical schema
- which secondary SQL artifacts are intended to supplement or replace parts of `database/schema.sql`

## Missing Indexes or Improvement Candidates

These are non-breaking improvement candidates, not mandatory schema changes.

1. Add supporting indexes for frequent cross-module reporting joins if query plans show table scans.
2. Consider indexes on common date-range filters across donations, events, and courses if reporting is slow.
3. Consider a generated-column or normalized strategy for JSON-heavy query paths if dashboard and report endpoints expand.
4. Consider explicit composite indexes where filters naturally combine scope plus status plus date.

## Non-Breaking Improvement Sequence

1. Reconcile report queries against canonical schema column names.
2. Document all polymorphic scope conventions in code and docs.
3. Add low-risk indexes based on observed query plans.
4. Standardize which tables are authoritative for event participation and donor identity.
5. Only then consider deeper normalization of JSON-heavy fields.