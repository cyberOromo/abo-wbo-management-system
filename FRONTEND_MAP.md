# Frontend Map

## Frontend Architecture Summary

The frontend is primarily server-rendered PHP with Bootstrap 5.3 styling, jQuery-based behavior, and selective `fetch()` calls for dynamic interactions. There is no evidence of a separate SPA frontend. The main frontend risk is not framework complexity; it is divergence between active templates, referenced assets, and registered backend endpoints.

## Active View Tree

The active default render tree is:

- `resources/views`

Key active view areas include:

- `resources/views/auth`
- `resources/views/dashboard`
- `resources/views/hierarchy`
- `resources/views/users`
- `resources/views/tasks`
- `resources/views/meetings`
- `resources/views/events`
- `resources/views/donations`
- `resources/views/reports`
- `resources/views/responsibilities`
- `resources/views/settings`
- `resources/views/admin`
- `resources/views/member-registration`
- `resources/views/hybrid-registration`

## Parallel View Tree

`app/Views` contains a richer parallel UI surface with additional components and more ambitious AJAX patterns. It includes views for:

- auth
- dashboard
- tasks
- meetings
- events
- donations
- reports
- courses
- users
- layouts
- components

This tree is not the default active render path. It should be treated as a parallel implementation branch until controllers are explicitly aligned to it.

## Shared Layouts and Reusable UI Blocks

### Confirmed Patterns

- layout-style templates exist under both `resources/views/layouts` and `app/Views/layouts`
- admin-facing compositions appear in `resources/views/admin/*` and parallel admin layout work in `app/Views/layouts/admin.php`
- shared navigation/component behavior is more developed in `app/Views/components/*`

### Practical Interpretation

- `resources/views` defines what the active application likely renders now
- `app/Views` defines what future consolidation might target, but is not safe to assume as active behavior

## Bootstrap Usage

The application uses Bootstrap-oriented layouts and admin/dashboard page structure across auth, management pages, and registration flows.

### Observed Usage Themes

- form-heavy CRUD screens
- table/list management interfaces
- dashboard cards and metric summaries
- modal or action-button patterns for assignment and management workflows
- calendar/scheduler style integrations for meetings and events in some parallel views

## jQuery and JavaScript Behaviors

### Confirmed Behavior Style

- traditional DOM-driven page scripts
- inline scripts embedded in templates
- jQuery-enhanced forms and page interactions
- direct `fetch()` calls for some dynamic dropdowns and dashboard-like interactions

### Hybrid Registration Assets

The only confirmed published assets under `public/assets` are:

- `public/assets/js/hybrid-registration.js`
- `public/assets/js/hybrid-registration-complete.js`
- `public/assets/css/hybrid-registration.css`

This makes the hybrid registration flow one of the best-aligned frontend surfaces in the repo.

## AJAX Interactions

### Confirmed Active-Tree AJAX Calls

From `resources/views`:

- `resources/views/hierarchy/tree.php` calls `/hierarchy/api/tree-data`
- `resources/views/responsibilities/assign.php` calls `/api/users`
- `resources/views/responsibilities/assign.php` calls `/api/users/{id}/positions`
- `resources/views/responsibilities/assign.php` calls `/api/responsibilities/available`

### Confirmed Parallel-Tree AJAX Calls

From `app/Views`, the frontend expects a much broader API layer including endpoints such as:

- `/api/dashboard/refresh`
- `/api/search`
- `/api/notifications`
- `/api/notifications/mark-all-read`
- `/api/tasks/assign`
- `/api/tasks/{id}/status`
- `/api/users/available-for-assignment`
- `/api/meetings/attendance`
- `/api/events/rsvp`
- `/api/events/calendar`
- `/api/reports/generate`
- `/api/reports/export/{type}`
- `/api/courses/enroll`
- `/api/donations/*`

### Frontend/API Alignment Assessment

- hierarchy-related AJAX is relatively well aligned with active routes
- responsibilities and many richer module screens expect endpoints that are not clearly registered in `routes/web.php`
- this mismatch should be treated as a continuity and QA risk before reviving or extending the richer UI surfaces

## Asset Map

### Confirmed Published Assets

- `public/assets/js/hybrid-registration.js`
- `public/assets/js/hybrid-registration-complete.js`
- `public/assets/css/hybrid-registration.css`

### Confirmed Missing or Unpublished References

Active templates reference assets such as:

- `/assets/css/bootstrap.min.css`
- `/assets/js/bootstrap.bundle.min.js`

Parallel templates also reference assets such as:

- `/assets/css/auth.css`

These were not found in `public/assets` during validation. This means some views depend on asset publication steps or missing files that are not currently present in the public tree.

## Reusable UI Blocks

### Most Reusable Surfaces

- hierarchy tree and scope-oriented pages
- auth forms
- admin and dashboard layouts
- registration-oriented forms with dependent dropdown behavior

### Most Fragmented Surfaces

- dashboard/admin component reuse across `resources/views` and `app/Views`
- AJAX-based controls in `app/Views`
- asset references that assume a fuller public asset bundle than currently exists

## UX Inconsistencies

1. Two parallel view trees suggest inconsistent visual and interaction patterns.
2. Some modules have only basic CRUD views in the active tree while richer versions exist elsewhere.
3. Active templates reference assets that are not currently published.
4. Frontend API expectations exceed the confirmed active backend endpoint surface.
5. Administrative and reporting experiences are likely uneven across modules due to partial implementation maturity.

## Frontend Continuation Priorities

1. Decide whether `resources/views` remains authoritative or whether selected `app/Views` screens should be promoted.
2. Reconcile template asset references with actual published assets.
3. Inventory and either implement or remove unserved `/api/*` dependencies.
4. Standardize shared layouts and navigation components across admin-facing screens.
5. Only then do broader UX modernization work.