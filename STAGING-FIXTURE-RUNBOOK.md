# Staging Fixture Runbook

This runbook documents the retained staging regression fixtures for the immutable internal email rollout.

## Purpose

Use these accounts to validate:
- role-based dashboard landing
- role-based module visibility
- immutable primary login
- preserved alias login for executive and admin office holders
- staging-safe reseeding with `database/seed_staging_internal_email_users.php --apply`

## Default Password

All staging fixtures use:

`Stage123!`

## Accounts

| Purpose | User Type | Scope | Personal Email | Primary Login | Expected Dashboard | Alias Login |
|---|---|---|---|---|---|---|
| Primary global admin alias case | `system_admin` | `global` | `staging.bontu.regassa@example.test` | `bontu.r@j-abo-wbo.org` | System Administration Dashboard | `staging_global_admin.global@j-abo-wbo.org` |
| Pure system admin | `system_admin` | `global` | `staging.system.admin@example.test` | `aster.d@j-abo-wbo.org` | System Administration Dashboard | none |
| Secondary admin coverage | `system_admin` | `global` | `staging.admin.ops@example.test` | `jawar.b@j-abo-wbo.org` | System Administration Dashboard | `staging_global_admin_ops.global@j-abo-wbo.org` |
| Godina executive | `executive` | `godina` | `staging.bontu.roba@example.test` | `bontu.r1@j-abo-wbo.org` | Executive Dashboard | `staging_godina_leader.stgimm-god@j-abo-wbo.org` |
| Gamta executive | `executive` | `gamta` | `staging.gamachu.tola@example.test` | `gamachu.t@j-abo-wbo.org` | Executive Dashboard | `staging_gamta_leader.stgimm-gam@j-abo-wbo.org` |
| Gurmu executive | `executive` | `gurmu` | `staging.mulu.bekele@example.test` | `mulu.b@j-abo-wbo.org` | Executive Dashboard | `staging_gurmu_leader.stgimm-gur@j-abo-wbo.org` |
| Member baseline | `member` | `gurmu` | `staging.saba.lelisa@example.test` | `saba.l@j-abo-wbo.org` | Member Dashboard | none |

## Expected Module Visibility

### System Admin
- Dashboard
- Users
- Hierarchy
- Positions
- Responsibilities
- Tasks
- Meetings
- Events
- Donations
- Reports
- Settings

### Executive
- Dashboard
- Users
- Hierarchy
- Responsibilities
- Tasks
- Meetings
- Events
- Donations
- Reports

### Member
- Dashboard
- Tasks
- Meetings
- Events
- Donations
- Profile

Members should not see admin, system-admin, or executive-only navigation such as Users, Hierarchy, Positions, Reports, or Settings.

## Reseed and Verify

Run on staging from the repo root:

```bash
php database/seed_staging_internal_email_users.php --apply
php database/migrate_immutable_internal_emails.php
php database/migrate_immutable_internal_emails.php --apply
php database/verify_internal_email_migration.php --limit=20
```

## Notes

- Keep these fixtures in staging for repeatable regression testing.
- The seeder is idempotent, but multiple active global admin fixtures require distinct positions because `user_assignments` enforces a unique active position assignment per organizational unit.