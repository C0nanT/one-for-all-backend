# User Module

## Purpose

Owns the `User` Eloquent model and provides a simple listing endpoint. This module is imported by others — it is a dependency, not a standalone feature.

## Who imports from here

- **Auth module** — `User::query()->create()` and `UserResource` for register/login responses
- **PayableAccount module** — `User::query()` inside `PayableAccountRepository::getSummary()` to resolve payer names

## Key Files

```
Models/User.php                        ← HasApiTokens (Sanctum), Notifiable, 'password' => 'hashed' cast
Http/Controllers/UserController.php   ← GET /users
Http/Resources/UserResource.php       ← { id, name, email }
routes/api.php
```

## API Endpoints

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| GET | `/users` | Sanctum | Returns all users (no pagination) |

## Conventions & Constraints

- `UserResource` exposes only `id`, `name`, `email`. Never add `password` or token fields.
- `/users` returns all users without pagination — it is used to populate payer dropdowns in the frontend and must remain complete.
- `HasApiTokens` from Sanctum must stay on the model — removing it breaks authentication.
