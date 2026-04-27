# Modules

## Status Legend

- Complete: route, controller, schema, and active-view coverage look substantially aligned
- Partial: significant implementation exists, but coverage is incomplete or uneven
- Needs Repair: confirmed runtime or structural drift exists
- Unclear: implementation intent exists but active path is uncertain

## Module Matrix

| Module | Purpose | Main Files | Routes / Pages | Tables Used | Dependencies | Status |
| --- | --- | --- | --- | --- | --- | --- |
| Authentication | Login, registration, logout, password reset | `app/Controllers/AuthController.php`, `app/Middleware/AuthMiddleware.php`, `app/helpers.php`, `resources/views/auth/*` | `/auth/login`, `/auth/register`, `/auth/logout`, `/auth/forgot-password`, `/auth/reset-password/{token}` | `users`, `password_reset_tokens`, `email_verification_tokens`, `user_sessions` | helper auth/session/CSRF layer, `App\Utils\Database` | Partial |
| Dashboard | Role-based landing and summary pages | `app/Controllers/DashboardController.php`, `resources/views/dashboard/*` | `/`, `/dashboard` | `users`, `user_assignments`, plus module summary queries | auth helpers, middleware, database wrapper | Partial |
| User Management | CRUD and profile management for users | `app/Controllers/UserController.php`, `resources/views/users/*` | `/users/*`, profile edit and password routes | `users`, `gurmus`, `gamtas`, `godinas`, `user_roles`, `user_assignments` | hierarchy tables, auth, helper/session layer | Partial |
| Hierarchy Management | Manage global, godina, gamta, gurmu structure and views | `app/Controllers/HierarchyController.php`, `resources/views/hierarchy/*` | `/hierarchy/*`, including tree and multiple API-style endpoints | `globals`, `godinas`, `gamtas`, `gurmus`, `positions`, `responsibilities`, `user_assignments`, `responsibility_assignments` | active router, hierarchy views, JSON endpoints | Complete |
| Position Management | CRUD for positions and assignment workflow | `app/Controllers/PositionController.php`, `resources/views/positions/*` where present | `/positions/*`, `/positions/api/by-level` | `positions`, `user_assignments`, `users` | hierarchy, approval flow, database wrapper | Partial |
| Responsibility Management | Assign, track, and report responsibilities | `app/Controllers/ResponsibilityController.php`, `resources/views/responsibilities/*` | `/responsibilities/*` | `responsibilities`, `responsibility_assignments`, `individual_responsibilities`, `shared_responsibilities`, `users`, `positions` | hierarchy, users, assignment logic | Partial |
| Task Management | CRUD, assignment, status updates, comments | `app/Controllers/TaskController.php`, `resources/views/tasks/*`, parallel `app/Views/tasks/*` | `/tasks/*` | `tasks`, `task_comments`, `task_activities`, `users` | auth, database, possibly notifications | Partial |
| Meeting Scheduler | Meeting CRUD, participants, minutes | `app/Controllers/MeetingController.php`, `resources/views/meetings/*`, parallel `app/Views/meetings/*` | `/meetings/*` | `meetings`, `meeting_attendees`, `meeting_activities`, `users` | auth, scheduling, database wrapper | Partial |
| Event Management | Event CRUD, registration, calendar views | `app/Controllers/EventController.php`, `resources/views/events/*`, parallel `app/Views/events/*` | `/events/*`, calendar views | `events`, `event_registrations`, `event_participants`, `users` | auth, scheduling, frontend calendar patterns | Partial |
| User Email Management | Organizational email account management | `app/Controllers/UserEmailController.php`, likely `resources/views/user-emails/*` or adjacent admin views | `/user-emails/*` | Needs Clarification: likely external provider plus internal tracking | auth, admin/system operations | Unclear |
| Donation Tracking | Donation CRUD, reporting, campaigns | `app/Controllers/DonationController.php`, `resources/views/donations/*`, parallel `app/Views/donations/*` | `/donations/*`, donation reports | `donors`, `donation_campaigns`, `donations`, `users` | finance reporting, database wrapper | Partial |
| Course and Training | Courses, lessons, enrollments, progress | `app/Controllers/CourseController.php`, `resources/views/courses/*`, parallel `app/Views/courses/*` | `/courses/*` | `course_categories`, `courses`, `lessons`, `course_enrollments`, `lesson_progress`, `users` | content management, users, progress tracking | Partial |
| Notifications | In-app notifications and read states | `app/Controllers/NotificationController.php`, `resources/views/notifications/*` if present | `/notifications/*` | `notifications`, `users` | auth, sender/recipient logic | Partial |
| Reports | Cross-module reporting and export | `app/Controllers/ReportController.php`, `resources/views/reports/*`, parallel `app/Views/reports/*` | `/reports/*`, `/reports/export/{type}` | cross-module reads from `users`, `hierarchy`, `tasks`, `meetings`, `events`, `donations`, `courses` | every business module, DB schema alignment | Needs Repair |
| Member Registration | Register members and manage membership records | `app/Controllers/MemberRegistrationController.php`, `resources/views/member-registration/*`, `resources/views/members/*` where present | `/member-registration/*` | `users`, `gurmus`, `gamtas`, `godinas`, possibly `user_assignments` | hierarchy access rules, auth, DB wrapper | Partial |
| User and Leader Registration | System-admin-driven user or leader onboarding | `app/Controllers/UserLeaderRegistrationController.php`, `resources/views/admin/user_leader_registration.php` | `/admin/user-leader-registration/*` | `users`, `user_assignments`, `positions`, `gurmus`, `gamtas`, `godinas` | system admin auth, email, logger, transactions | Partial |
| System Administration | Global settings and top-level administrative control | `app/Controllers/SystemAdminController.php`, `resources/views/admin/*` | `/admin/*` | `system_settings`, `users`, hierarchy tables, possibly logs | system admin middleware, render coverage | Partial |
| Settings | Application/system settings surfaces | `app/Controllers/SettingsController.php` where present, `resources/views/settings/*` | Settings pages where routed or linked | `system_settings` | auth, admin role checks | Unclear |
| Audit and Logging | Audit trail and system log capture | models/services around logs, schema support | indirect, not clearly exposed as active UI | `audit_logs`, `system_logs` | user actions, system services | Unclear |
| File Upload Handling | Persist uploaded files and metadata | `file_uploads` schema support, upload config in `config/app.php` and `config/storage.php` | indirect, attached to other modules | `file_uploads` | storage config, access controls, scan lifecycle | Unclear |
| API / AJAX Layer | JSON-style endpoints for dynamic frontend interactions | `routes/web.php`, hierarchy and position controllers, helper JSON responses | hierarchy API routes and scattered frontend expectations | depends on module tables | router, controllers, frontend JS | Needs Repair |

## Module Notes

### Authentication

- The active auth path is helper-driven and controller-driven, not clearly service-driven.
- Middleware exists, but admin-role enforcement is likely broken because of object-versus-array assumptions.
- CSRF handling is split across helper and middleware implementations.

### Dashboard

- The dashboard is central to role-based navigation.
- Some helper methods and statistics paths appear simplified or placeholder-like.
- Dashboard refresh/API expectations are richer in `app/Views` than in active routes.

### Hierarchy Management

- This is the most mature module in the codebase.
- It has the clearest controller depth, view coverage, and explicit API endpoints.
- It also acts as a dependency for users, registrations, responsibilities, and access-scope logic.

### Reports

- Reports are structurally central but currently unstable.
- Live validation with MySQL running showed a query failure referencing `m.start_date`, which does not match the schema shape of the `meetings` table.
- This module should be treated as repair-first before broader feature expansion.

### Parallel UI Surfaces

- Several modules have richer views in `app/Views` than in `resources/views`.
- Unless controllers are intentionally moved to those templates, documentation and continuation work should treat `resources/views` as authoritative.

## Cross-Module Dependencies

- Hierarchy underpins users, assignments, registration, and scope-aware reporting.
- Users and `user_assignments` drive executive access and responsibility mapping.
- Notifications, reports, and dashboards depend on stable outputs from other modules.
- Asset publication and API coverage affect many modules simultaneously, especially if `app/Views` is reactivated.

## Recommended Module Continuation Order

### Execution Principle

Work one dependency chain at a time. Do not mix unrelated module completion work in the same staging cycle unless the later change is required to unblock the active lane.

### Recommended Order

1. Shared runtime blockers affecting active staging paths
2. Reports repair and admin contract stabilization
3. User management plus registration flows tied to hierarchy assignment
4. Position and responsibility workflows
5. Tasks, meetings, events, and donations UX completion on the active render tree
6. Courses, notifications, and secondary modules
7. System admin, settings, audit/log surfaces

### Current Practical Order

1. Finish the current tasks, meetings, events, and donations stabilization slice already in progress.
2. Move immediately to member registration and user-leader registration with hierarchy-linked assignment validation.
3. After registration is proven on staging, return to public debug-script lockdown and the remaining Phase 1 hardening tasks.