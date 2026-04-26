# Continuation Roadmap

## Planning Assumptions

- Preserve HostGator staging compatibility.
- Avoid schema-breaking changes unless explicitly requested.
- Treat active runtime path as `public/index.php -> App\Core\Application -> App\Core\Router -> routes/web.php`.
- Treat `resources/views` as the active render tree until a migration plan says otherwise.
- Use `database/schema.sql` as the canonical schema source.

## Phase 1: Stabilize Current Staging System

### Goal

Reduce immediate operational and security risk without changing the platform shape.

### Tasks

| Task | Dependencies | Complexity | Recommended Order |
| --- | --- | --- | --- |
| Remove or block public debug/test scripts | none | Medium | 1 |
| Standardize local and staging environment bootstrap expectations | none | Medium | 2 |
| Fix admin auth contract mismatches | auth helper path validation | Medium | 3 |
| Repair report queries against canonical schema | DB schema review | Medium | 4 |
| Align active templates with actual published assets | asset inventory | Low to Medium | 5 |
| Confirm active CSRF path and apply consistently to existing forms | auth/form audit | Medium | 6 |

### Exit Criteria

- staging no longer exposes operational diagnostics publicly
- login/admin/report flows are stable on the active path
- active templates do not depend on missing assets

## Phase 2: Complete Missing Core Modules

### Goal

Bring route, controller, view, and data coverage into alignment for the modules already represented in the codebase.

### Tasks

| Task | Dependencies | Complexity | Recommended Order |
| --- | --- | --- | --- |
| Reconcile user management CRUD views with active route coverage | Phase 1 complete | Medium | 1 |
| Complete position and responsibility workflow gaps | hierarchy stable | High | 2 |
| Repair tasks, meetings, and events active view coverage | asset/API alignment | High | 3 |
| Reconcile member and user-leader registration flows | auth and hierarchy stable | High | 4 |
| Validate notifications and settings active surfaces | auth/admin consistency | Medium | 5 |

### Exit Criteria

- active routes do not point to missing or clearly incomplete views
- major business modules have a usable active UI path
- dependency chains across hierarchy, users, and assignments are stable

## Phase 3: Refactor Shared Architecture

### Goal

Reduce duplicated abstractions and normalize runtime contracts.

### Tasks

| Task | Dependencies | Complexity | Recommended Order |
| --- | --- | --- | --- |
| Choose one authoritative session/auth state model | Phase 2 core stability | High | 1 |
| Unify CSRF implementation | auth/session decision | High | 2 |
| Normalize database wrapper usage and prepared-statement interface | module query audit | High | 3 |
| Consolidate route and controller conventions | active surface inventory | Medium | 4 |
| Decide future of `app/Views` versus `resources/views` | frontend inventory | High | 5 |
| Centralize hierarchy scope logic | hierarchy module maturity | Medium | 6 |

### Exit Criteria

- one clear auth/session/CSRF contract exists
- one clear render tree is authoritative
- one clear database access contract is used across modules

## Phase 4: Optimize Performance and UX

### Goal

Improve usability, consistency, and performance after the active system is reliable.

### Tasks

| Task | Dependencies | Complexity | Recommended Order |
| --- | --- | --- | --- |
| Rationalize or implement missing AJAX endpoints | Phase 3 frontend/backend alignment | Medium | 1 |
| Improve dashboard and reporting performance with query/index review | stable reports and data paths | Medium to High | 2 |
| Standardize layouts, forms, tables, and navigation patterns | chosen render tree | Medium | 3 |
| Audit and improve mobile responsiveness on active screens | frontend stabilization | Medium | 4 |
| Reduce inline-script duplication in templates | layout/component consolidation | Medium | 5 |

### Exit Criteria

- UX is consistent across main admin and member workflows
- dashboard and reports are performant enough for staging sign-off
- frontend dependencies are intentional and documented

## Phase 5: Production Readiness

### Goal

Move from resumed development to reliable release discipline.

### Tasks

| Task | Dependencies | Complexity | Recommended Order |
| --- | --- | --- | --- |
| Restore or install executable test tooling in the workspace | dependency hygiene | Medium | 1 |
| Make PHPUnit and smoke tests part of a reliable validation workflow | test tooling present | Medium | 2 |
| Define deployment-safe secrets and config management | hosting conventions | Medium | 3 |
| Review file permissions, log handling, and backup procedures | deployment process | Medium | 4 |
| Produce a final canonical operations checklist for staging and production | all prior phases | Low to Medium | 5 |

### Exit Criteria

- repeatable validation exists before deployment
- environment-specific secrets are handled safely
- staging and production operations are documented and controlled

## Recommended Resume Sequence

1. Lock down public exposure and stabilize reports.
2. Fix auth/session/admin contract issues.
3. Complete the active `resources/views` path for key modules before touching `app/Views`.
4. Reconcile database access and CSRF architecture.
5. Only after stabilization, choose whether to modernize or retire the parallel UI surfaces.

## Complexity Summary

- Low: asset alignment, documentation, checklist work
- Medium: route/view reconciliation, admin auth fixes, test-tool restoration
- High: report repair across modules, auth/session unification, render-tree consolidation, DB contract normalization

## Immediate Next Sprint Recommendation

1. Remove or block public debug scripts.
2. Fix `AdminMiddleware` and validate admin access.
3. Fix report queries against canonical schema.
4. Inventory and repair active missing assets.
5. Confirm one CSRF path for the existing active forms.