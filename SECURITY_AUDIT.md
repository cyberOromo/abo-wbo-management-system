# Security Audit

## Scope and Method

This audit is based on repository analysis plus limited live local validation. The findings prioritize confirmed architectural and operational risks, especially those that affect the current staging-compatible runtime path.

Severity levels:

- Critical
- High
- Medium
- Low

## Critical Findings

### 1. Public diagnostic and debug exposure

Confirmed risk: the public document root contains many diagnostic, test, and debug scripts in addition to the real front controller.

Examples include:

- `public/verify-env.php`
- `public/debug-login.php`
- `public/diagnostic.php`
- `public/system-status.php`
- multiple `public/test-*.php` files

Impact:

- environment leakage
- schema or runtime information disclosure
- easier attack-surface discovery
- accidental bypass of normal routing and middleware assumptions

Recommendation:

1. remove these from the public document root, or
2. block them in server config immediately, or
3. gate them by environment and authentication if temporary retention is unavoidable

## High Findings

### 2. Split CSRF implementations

Confirmed risk: CSRF logic exists in both helper/controller flows and a separate `CsrfMiddleware` path with different token handling assumptions.

Impact:

- inconsistent protection across forms and routes
- difficult verification of which POST/PUT/DELETE flows are actually protected
- likely regressions when adding or repairing forms

Recommendation:

- choose one authoritative CSRF mechanism and apply it consistently across controllers, middleware, and templates

### 3. Admin authorization contract mismatch

Confirmed risk: `AdminMiddleware` expects a user object with `hasRole()`, while the active helper path appears to return arrays from `auth_user()`.

Impact:

- broken authorization checks
- false denies or fragile error behavior on admin routes
- potential future bypasses if fixes are made inconsistently

Recommendation:

- normalize the authenticated-user contract and centralize role checks in one verified path

### 4. Database access contract mismatch

Confirmed risk: the active database wrapper does not implement `prepare()`, yet some call sites assume it does.

Impact:

- broken secure query usage
- pressure toward unsafe query fallbacks
- inconsistent prepared-statement behavior across modules

Recommendation:

- either add a safe prepared-statement interface to `App\Utils\Database` or refactor call sites to the wrapper's supported methods only

### 5. Frontend/API drift on sensitive workflows

Confirmed risk: several active or parallel templates expect `/api/*` endpoints that are not clearly registered in the active route file.

Impact:

- broken client-side validation or lookup flows
- partial admin actions
- security assumptions split between frontend expectations and backend availability

Recommendation:

- inventory active frontend calls and align them strictly with registered routes

## Medium Findings

### 6. XSS risk from mixed template and helper patterns

Evidence level: moderate. The codebase uses server-rendered templates, inline scripts, and large helper surfaces. That increases the chance of inconsistent escaping, especially in templates that evolved outside one strict rendering abstraction.

Impact:

- reflected or stored XSS where user-controlled values are rendered without consistent escaping

Recommendation:

- standardize output escaping rules and audit templates that inject dynamic values into HTML attributes, script blocks, and inline JSON

### 7. Session model fragmentation

Confirmed risk: helper-driven session handling and `App\Utils\Session` coexist without a clearly unified active contract.

Impact:

- inconsistent session hardening
- fingerprinting/rotation protections applied unevenly
- difficult reasoning about logout, flash, and auth persistence behavior

Recommendation:

- standardize one session implementation and move all auth/session operations through it

### 8. File upload handling needs explicit hardening review

Evidence level: moderate. The schema and config support upload tracking, permissions, and scan status, but active upload-handling code paths were not the main validated runtime slice.

Impact:

- unsafe MIME handling
- executable upload exposure
- orphaned files or direct-public-path exposure

Recommendation:

- verify storage location, MIME validation, extension allowlists, public/private separation, and scanning behavior before expanding upload features

### 9. Error leakage via operational scripts and ad hoc checks

Confirmed risk: several scripts are clearly designed for debugging or environment validation.

Impact:

- internal paths, DB config behavior, schema details, and runtime state could leak to unauthorized users

Recommendation:

- remove public access, centralize logging, and disable display of detailed errors outside local development

## Low Findings

### 10. Schema and SQL artifact drift

Risk: multiple schema or migration artifacts can cause operators or developers to apply inconsistent SQL changes.

Impact:

- accidental weakening of constraints or inconsistent environments

Recommendation:

- formally declare `database/schema.sql` the canonical source and classify other SQL files

### 11. Asset publication drift

Risk: templates reference assets not present in the public asset tree.

Impact:

- broken client-side validation or UI behaviors
- users may interact with partially protected or partially functioning forms

Recommendation:

- align template references with published assets or restore the missing files through the intended build/publication path

## SQL Injection Risk Assessment

### Confirmed posture

- the system uses PDO through `App\Utils\Database`
- the schema and app structure show intent toward structured DB access

### Confirmed concern

- call sites expecting `prepare()` on a wrapper that does not implement it mean the secure-query contract is not consistent

Assessment:

- SQL injection risk cannot be declared low until all query construction patterns are reconciled with one verified prepared-statement approach

## CSRF Risk Assessment

Assessment: High

Reason:

- overlapping CSRF mechanisms create uncertainty about consistent enforcement

## Session Weakness Assessment

Assessment: Medium to High

Reason:

- fragmentation between helper-driven and utility-class session management makes hardening uneven

## Exposed Config and Environment Leakage

Assessment: Critical to High depending on which public scripts remain accessible.

Reason:

- diagnostic scripts in `public/` are a direct exposure route

## Recommended Security Fix Order

1. Remove or block public debug and test scripts.
2. Unify CSRF handling.
3. Normalize auth and admin role checks.
4. Normalize database prepared-statement usage.
5. Audit active templates for escaping and inline-script data injection.
6. Review upload handling and storage exposure.
7. Reconcile public assets and frontend/API assumptions for admin workflows.