# AI Notes

## Naming and Structural Conventions

- Controllers generally follow `SomethingController` naming and are referenced as `Controller@method` in routes.
- Namespaces broadly follow PSR-4 under `App\...`.
- Helpers in `app/helpers.php` act as a de facto internal platform layer and are used pervasively.
- Table naming is pluralized and mostly conventional.
- Organizational vocabulary is domain-specific: `global`, `godina`, `gamta`, `gurmu`.

## Coding Style Patterns

- Mix of custom MVC conventions and procedural helper usage
- direct database access through a singleton utility rather than a full ORM
- inline controller logic is common
- server-rendered PHP templates with embedded JavaScript are common
- configuration is a mix of helper-based reads and direct file inclusion

## Repeated Logic Worth Refactoring

1. Session and auth state handling are split across helpers, middleware, and `App\Utils\Session`.
2. CSRF logic is duplicated between helper/controller paths and `CsrfMiddleware`.
3. Route-to-view resolution assumptions are duplicated across active and parallel view trees.
4. Hierarchy-derived access scope logic appears in multiple areas and should be centralized.
5. Database usage patterns are inconsistent, especially where code assumes `Database::prepare()` exists.

## Security Concerns

### Critical

- public debug and diagnostic scripts are exposed under `public/`

### High

- split CSRF implementation creates inconsistent protection guarantees
- auth/admin role checks likely contain runtime contract mismatches
- asset and API drift can expose admin workflows to partial or broken client-side protections

### Medium

- duplicated helper logic increases the chance of inconsistent escaping, validation, and session behavior
- file upload table and config are present, but upload-handling hardening should be explicitly verified before expansion

## Technical Debt Themes

### Architectural Drift

- active runtime path and richer parallel abstractions coexist without a clear consolidation boundary
- `resources/views` and `app/Views` split frontend implementation effort
- router and service abstractions are only partially normalized

### Operational Drift

- many test/debug scripts remain in the repo and public root
- SQL and migration artifacts are fragmented
- deployment guidance is broad, but the primary live target is still traditional shared hosting

### Runtime Contract Drift

- reports query logic does not fully match canonical schema
- middleware assumptions do not fully match helper return types
- frontend API calls do not fully match active route registration

## Fastest Wins for Cleanup

1. Remove or restrict public debug and diagnostic scripts.
2. Declare `resources/views` as the sole active render tree until a migration plan exists.
3. Fix `AdminMiddleware` and similar auth-contract mismatches.
4. Repair report queries against `database/schema.sql`.
5. Publish or remove missing asset references in active templates.
6. Document which SQL artifacts are canonical versus historical.
7. Either implement missing API endpoints used by active views or remove those frontend dependencies.

## Engineering Guidance for Continuation

- Treat `database/schema.sql`, `public/index.php`, `app/Core/Application.php`, `app/Core/Router.php`, and `routes/web.php` as the primary continuity anchor points.
- Do not revive `app/Views` wholesale without first reconciling routes, assets, and API endpoints.
- Prefer root-cause fixes over local patches where drift has been confirmed.
- Preserve HostGator/shared-hosting compatibility while stabilizing the codebase.

## Needs Clarification Before Large Refactors

- Whether the richer `app/Views` tree is strategic or abandoned
- Whether `AuthService` and `App\Utils\Session` are intended future abstractions
- Whether some admin/system modules depend on external systems not represented in the schema