# Project Context

## Executive Summary

ABO-WBO is a custom PHP 8.2+ organizational management platform for a multi-level hierarchy that includes authentication, user administration, hierarchy management, position and responsibility assignment, tasks, meetings, events, donations, courses, notifications, reports, and registration workflows. The system is already deployed to a HostGator staging environment and appears to have been developed iteratively over time, resulting in a functional active runtime path plus several parallel or partially superseded implementation surfaces.

The active application path is a custom MVC-style stack:

- `public/index.php` is the front controller.
- `App\Core\Application` bootstraps config, helpers, session, services, middleware, and routes.
- `App\Core\Router` is the active router.
- `routes/web.php` is the active route registry.
- `resources/views` is the active default render tree.

The codebase is not a framework application in the Laravel or Symfony sense. It is a custom modular MVC/procedural hybrid with some duplicated abstractions and partially overlapping implementations. The main engineering challenge is not lack of breadth; it is drift between routes, views, assets, SQL assumptions, and older parallel abstractions.

## Confirmed Tech Stack

### Backend

- PHP 8.2+
- Composer for autoloading and package management
- Custom application bootstrap and router
- PDO-based MySQL access wrapped by `App\Utils\Database`

### Database

- MySQL 8.0+
- Canonical schema source is `database/schema.sql`
- Runtime database checks succeeded locally after starting XAMPP MySQL

### Frontend

- Bootstrap 5.3
- jQuery
- Server-rendered PHP templates
- JavaScript `fetch()` calls for some AJAX-style interactions

### Hosting and Deployment

- HostGator shared hosting / cPanel-style staging is the primary target environment
- Local development uses XAMPP on Windows
- Docker and GitHub Actions files exist but do not appear to be the primary live deployment path

## Folder Structure

### Core Runtime Areas

- `public/`: public document root, front controller, and many exposed debug/test scripts
- `app/Core/`: active application bootstrap and active router
- `app/Controllers/`: controller layer
- `app/Models/`: model layer where present
- `app/Services/`: service abstractions, some active and some parallel
- `app/Middleware/`: middleware classes registered by `Application`
- `app/Utils/`: database wrapper, session abstraction, and older utility surfaces
- `app/helpers.php`: heavily used global helper layer
- `routes/`: route definitions, with `web.php` confirmed as the active route file
- `resources/views/`: active default view tree

### Parallel or Legacy-Like Areas

- `app/Views/`: richer parallel view tree, not the default render path
- `app/Utils/Router.php`: parallel router implementation, not on the active front-controller path
- multiple debug, migration, verification, and one-off scripts at repo root and under `public/`

### Database and Testing

- `database/schema.sql`: canonical schema base
- `database/migrate.php`: main setup/bootstrap script
- `database/`: additional SQL and migration artifacts, some overlapping and potentially stale
- `tests/`: PHPUnit suites for Unit, Integration, and Feature tests
- `phpunit.xml`: strict PHPUnit configuration targeting an in-memory SQLite test environment

## Architecture Style

The system is best described as a custom modular MVC/procedural hybrid.

### What is clearly active

- PSR-4 Composer autoloading via `App\` and `Database\`
- a custom `Application` bootstrap
- a custom `Router`
- controller classes with `Controller@method` route handlers
- global helper functions for env, config, auth, session, redirect, and CSRF
- direct database usage through `App\Utils\Database`

### What introduces complexity

- duplicated routing abstractions
- split configuration loading patterns
- split session/auth/CSRF patterns
- parallel `resources/views` and `app/Views` trees
- many operational scripts in public and repo root

## Request Flow

### Active Request Path

1. Web request enters through web server rewrite rules.
2. Root `.htaccess` rewrites requests toward `public/index.php` and blocks some sensitive directories.
3. `public/index.php` defines runtime constants, loads Composer autoloading and helpers, loads environment values, and boots `App\Core\Application`.
4. `App\Core\Application` loads configuration, initializes services and middleware, loads routes, and calls the active router.
5. `App\Core\Router` normalizes the request path, matches route definitions from `routes/web.php`, runs middleware, and dispatches controller handlers.
6. Controllers render views, return redirects, or emit JSON-style responses depending on the route.

### Active vs Non-Active Surfaces

- `App\Core\Router` is active.
- `App\Utils\Router` appears to be a parallel or older implementation.
- `resources/views` is the active default render path.
- `app/Views` contains richer UI work but is not the default active view tree.

## Frontend to Backend Communication

### Server-Rendered Pages

Most module pages are rendered as PHP templates through controller actions.

### AJAX and API-Style Calls

There is a small confirmed API-style surface in `routes/web.php`, mainly under hierarchy and one positions endpoint:

- `/hierarchy/api/tree-data`
- `/hierarchy/api/hierarchy`
- `/hierarchy/api/organizational-path`
- `/hierarchy/api/user-access-scope`
- `/hierarchy/api/validate-integrity`
- `/hierarchy/api/individual-responsibilities`
- `/hierarchy/api/shared-responsibilities`
- `/hierarchy/api/responsibility-matrix`
- `/hierarchy/api/create-missing-responsibilities`
- `/hierarchy/api/assign-user-position`
- `/positions/api/by-level`

The active view tree also contains templates that expect broader `/api/*` coverage that is not clearly registered in the active router. The parallel `app/Views` tree expects a much larger AJAX/API surface for tasks, meetings, events, reports, notifications, search, donations, and dashboard refresh. This mismatch is one of the clearest continuity risks.

## Authentication and Session Flow

### Confirmed Active Path

- `AuthController` handles login, register, logout, forgot password, and reset password routes.
- `app/helpers.php` provides the currently active session and auth helpers such as `auth_check()`, `auth_user()`, `session_get()`, and related helpers.
- `AuthMiddleware` enforces login and returns either a redirect or JSON 401 depending on the request.

### Important Runtime Observations

- Login logic supports current password data through the `password` column, with logic that also references `password_hash` patterns.
- Logout currently uses `session_destroy()` style behavior.
- A richer `App\Services\AuthService` exists but is not clearly the active route-layer path.
- A richer `App\Utils\Session` exists but is not clearly the active primary session contract.
- `AdminMiddleware` calls `$user->hasRole('admin')`, but `auth_user()` appears to return an array on the active helper-driven path. This is a likely runtime contract bug.

### CSRF State

There are two overlapping CSRF approaches:

- helper/controller CSRF logic driven by helpers such as `csrf_token()` and verification helpers
- `CsrfMiddleware` with its own token naming and expiry model

These patterns are not fully unified and should be treated as a repair area.

## Database Access Pattern

### Confirmed Runtime Pattern

- `App\Utils\Database` is the active database wrapper over PDO.
- It reads its main config from `config('database')`, which in practice comes from `config/app.php` via the helper path.
- Scripts and diagnostics also directly include `config/database.php`.

### Important Constraint

`App\Utils\Database` does not implement a `prepare()` method, but multiple call sites assume it exists. This is a confirmed application contract mismatch and should be documented as a repair item.

### Canonical Schema

`database/schema.sql` is the best canonical schema source for documentation and future alignment work. The repo contains many other SQL scripts, but those should be treated as overlays, utilities, or drift until individually validated.

## Composer Autoloading Structure

Composer confirms:

- `App\` maps to `app/`
- `Database\` maps to `database/`
- `app/helpers.php` is autoloaded

This means helper functions are globally available very early in the runtime and are part of the effective public internal API of the application.

## Deployment Notes for HostGator

### Confirmed Deployment Characteristics

- HostGator shared hosting / cPanel-style staging is the main compatibility target.
- `.htaccess` and public document root behavior matter more than container-centric assumptions.
- Multiple deployment guides in the repo are aligned to HostGator or cPanel-style workflows.

### Deployment Risks

- `public/` contains many diagnostic and test scripts that should not remain exposed in a staging or production document root.
- Asset publication is incomplete relative to template references.
- Runtime health depends on MySQL availability; local validation only succeeded after starting XAMPP MySQL.

### Live Environment Validation Notes

- Local MySQL was initially down and port 3306 was not listening.
- After starting XAMPP MySQL, `public/test-database.php` succeeded.
- `test_admin_modules.php` then passed Tasks, Meetings, Events, and Donations, and failed only in Reports because of a query/schema mismatch referencing `m.start_date`.

## Current System State Summary

### Strongly Confirmed

- active front controller and router path
- active default view tree is `resources/views`
- canonical schema source is `database/schema.sql`
- hierarchy module has the most mature route/API coverage
- local DB connectivity works when MySQL is started

### Needs Repair

- route/view/API drift across modules
- public debug and diagnostic exposure
- duplicate or competing abstractions for router, session, CSRF, and auth
- missing public assets referenced by templates
- reports query mismatch against actual schema

### Needs Clarification

- which parallel `app/Views` surfaces are intended for revival versus retirement
- whether `AuthService` and `Session` are future intended abstractions or abandoned branches
- which SQL artifacts beyond `database/schema.sql` are still authoritative