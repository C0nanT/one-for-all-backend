# PayableAccount Module

## Purpose

Manages shared monthly expenses. Each account tracks payments per period (who paid, how much) and can have free-form notes. Provides summary and count aggregations consumed by the dashboard.

## Dependencies

- **Imports from `User` module:** `Modules\User\Models\User` is queried inside `PayableAccountRepository::getSummary()` to resolve payer names.
- Route model binding for `{payment}` and `{note}` parameters is registered in `AppServiceProvider::boot()` — do not add `findOrFail` manually in controllers for these.

## Key Files

```
Models/
  PayableAccount.php          ← SoftDeletes, hasMany payments & notes
  PayableAccountPayment.php   ← amount (float), period (date), payer_id (nullable FK to users)
  PayableAccountNote.php      ← text, amount, date; belongsTo account and user

Services/
  PayableAccountService.php         ← list, getSummary, getPaidUnpaidPaidZeroCounts, CRUD
  PayableAccountPaymentService.php  ← store, update payment
  PayableAccountNoteService.php     ← store, update, delete note

Repositories/  (all implement interfaces in Contracts/Repositories/)
  PayableAccountRepository.php        ← getAll(period), getSummary(period), getPaidUnpaidPaidZeroCounts(period)
  PayableAccountPaymentRepository.php
  PayableAccountNoteRepository.php

Http/Controllers/
  PayableAccountController.php        ← CRUD + counts endpoint
  PayableAccountPaymentController.php ← store and update
  PayableAccountNoteController.php    ← CRUD

Http/Requests/   ← StoreXxx and UpdateXxx for each entity (3 pairs)
Http/Resources/  ← PayableAccountResource, PayableAccountNoteResource
routes/api.php
```

## API Endpoints

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/payable-accounts?period=YYYY-MM` | List with summary |
| GET | `/payable-accounts/counts?period=YYYY-MM` | `{ paid, unpaid, paid_zero }` |
| POST | `/payable-accounts` | Create (`name` only) |
| GET | `/payable-accounts/{id}` | Single account |
| PUT | `/payable-accounts/{id}` | Update |
| DELETE | `/payable-accounts/{id}` | Soft delete |
| POST | `/payable-accounts/{id}/payments` | Record payment |
| PUT | `/payable-accounts/{id}/payments/{payment}` | Update payment |
| GET | `/payable-account-notes?period=YYYY-MM` | List notes for period |
| POST | `/payable-account-notes` | Create note |
| PUT | `/payable-account-notes/{note}` | Update note |
| DELETE | `/payable-account-notes/{note}` | Delete note |

## Statuses

- `unpaid` — no payment record for the period
- `paid` — payment with `amount > 0`
- `paid_zero` — payment exists but `amount = 0` (acknowledged as zero cost)

The status is derived in the repository query, not stored on the model.

## Adding a new endpoint (checklist)

1. Create `Http/Requests/StoreXxx.php` (or `UpdateXxx.php`) via `php artisan make:request`
2. If new data access logic is needed: add method to the interface in `Contracts/Repositories/`, then implement in `Repositories/`
3. Add method to the relevant `Service`
4. Add method to the `Controller`, inject service via constructor
5. Add the route in `routes/api.php` inside the `auth:sanctum` group
6. **Bind the new interface** in `app/Providers/AppServiceProvider::register()` if a new interface was created
7. Add a Pest feature test

## Pitfalls

- **Never query payments without a period filter.** `PayableAccountPayment` records accumulate across months — always scope by `period BETWEEN start AND end`.
- **`getAll` uses a "latest payment per account per period" subquery** (`MAX(id) GROUP BY payable_account_id, period`) to avoid duplicates. Replicate this pattern in any new payment query.
- **Soft deletes:** `PayableAccount` uses `SoftDeletes`. `withTrashed()` should only be used for admin/audit purposes.
- **`payer_id` is nullable** — a payment with `payer_id = null` is valid (anonymous payment).

## Tests

```bash
php artisan test --compact tests/Feature/PayableAccountTest.php
php artisan test --compact tests/Feature/PayableAccountAuthTest.php
php artisan test --compact tests/Feature/PayableAccountCountsTest.php
php artisan test --compact tests/Feature/PayableAccountPaymentTest.php
```
