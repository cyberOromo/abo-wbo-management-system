# Staging Internal Email Migration Guide

This guide stages the immutable-primary migration safely before production.

## Goal

Move existing users from hierarchy-bearing primary internal email addresses to immutable primary addresses in the format `firstname.lastInitial@j-abo-wbo.org`.

Preserve every previous primary address as an active alias that forwards to the new primary address.

Keep old login addresses valid during the transition by allowing authentication through active alias records.

## Order Of Operations

1. Seed staging-only hierarchy fixtures and test users.
2. Run the immutable-primary migration in dry-run mode.
3. Validate login with both new primary and old alias addresses.
4. Apply the migration in staging.
5. Re-run the same login and admin-email checks.

## Commands

Dry-run staging seed:

```bash
/c/xampp/php/php.exe database/seed_staging_internal_email_users.php
```

Apply staging seed:

```bash
/c/xampp/php/php.exe database/seed_staging_internal_email_users.php --apply
```

Dry-run immutable-primary migration:

```bash
/c/xampp/php/php.exe database/migrate_immutable_internal_emails.php
```

Apply immutable-primary migration:

```bash
/c/xampp/php/php.exe database/migrate_immutable_internal_emails.php --apply
```

Limit migration to one user while validating:

```bash
/c/xampp/php/php.exe database/migrate_immutable_internal_emails.php --user-id=42 --apply
```

## Staging Fixtures

The staging seed script creates isolated fixtures with codes prefixed by `STGIMM` and creates these user categories:

1. Global admin
2. Godina executive
3. Gamta executive
4. Gurmu executive
5. Gurmu member

It also intentionally creates a collision pair for immutable primary generation by seeding two different `Bontu R*` users.

Default seeded password: `Stage123!`

## Expected Results

After migration:

1. `users.internal_email` stores the immutable primary login.
2. The prior primary address remains in `internal_emails` as an active `alias` record.
3. The preserved alias forwards to the new primary address.
4. Login succeeds with either the new primary or the preserved old alias.
5. Admin email management continues to show primary and alias rows separately.