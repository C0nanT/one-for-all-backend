# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **PHP 8.3 / Laravel 12** (streamlined structure — no `Kernel.php`; middleware/routing in `bootstrap/app.php`)
- **PostgreSQL 16** (production), **SQLite in-memory** (tests)
- **Pest 4** for testing, **Pint** for formatting, **PHPStan/Larastan** for static analysis
- **Laravel Sanctum** for token-based API auth

## Common Commands

Run inside the container (`make sh` to open a shell):

```bash
# Tests
php artisan test --compact                         # full suite
php artisan test --compact --filter=TestName       # single test
php artisan test --compact tests/Feature/AuthTest.php  # single file

# Formatting (run before finalizing any PHP changes)
vendor/bin/pint --dirty --format agent

# Static analysis
vendor/bin/phpstan analyse --no-progress

# Database
php artisan migrate
```

Docker helpers (run on the host):

```bash
make up          # start containers
make sh          # shell into app container
make migrate     # run migrations inside container
make down        # stop containers
make backup-db   # dump PostgreSQL to backups/
make restore-db BACKUP_FILE=backups/backup-xxx.dump
```

## Architecture

The application uses a **modular monolith** pattern. Business domains live in `modules/` (not `app/`), each self-contained:

```
modules/
  Auth/
  User/
  PayableAccount/
  TransportCard/
    Http/Controllers/
    Http/Requests/      ← Form Request classes for validation
    Http/Resources/     ← Eloquent API Resources
    Contracts/Repositories/   ← interfaces
    Models/
    Repositories/       ← implementations bound in AppServiceProvider
    Services/
    routes/api.php
```

`routes/api.php` simply `require`s each module's own `routes/api.php`. All API routes are versioned and protected by Sanctum except auth endpoints.

`AppServiceProvider` is the single place where repository interfaces are bound to their implementations.

Tests use SQLite in-memory and live in `tests/Feature/` (most tests are feature tests hitting the full HTTP stack).

## Conventions

- Use `php artisan make:` for all new files; pass `--no-interaction`.
- Use `php artisan make:test --pest {Name}` for new tests.
- Validate via Form Request classes — never inline in controllers.
- Use Eloquent resources for all API responses.
- Avoid `DB::` — prefer `Model::query()`.
- Every change must have a passing Pest test.
- Run `vendor/bin/pint --dirty --format agent` before finishing any PHP work.

## Git Hooks

- **Pre-commit**: PHP syntax check, blocks debug calls (`dd`, `dump`, `ray`, `var_dump`), runs Pint.
- **Pre-push**: runs `php artisan test --compact`.
