# Auth Module

## Purpose

Handles user registration, login, and logout via Laravel Sanctum. Returns a plain-text token on register/login; the client sends it as `Authorization: Bearer <token>` on every subsequent request.

## Dependencies

- **Imports from `User` module:** `Modules\User\Models\User` (user creation and lookup) and `Modules\User\Http\Resources\UserResource` (response shape). Do not create a local User model.

## Key Files

```
Http/Controllers/AuthController.php   ← register, login, logout, user
Http/Requests/LoginRequest.php        ← email, password, device_name
Http/Requests/RegisterRequest.php     ← name, email, password, password_confirmation
routes/api.php
```

## API Endpoints

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| POST | `/register` | public | Creates user, returns `{ token, user }` — 201 |
| POST | `/login` | public | Returns `{ token }` |
| POST | `/logout` | Sanctum | Deletes current token, returns 204 |
| GET | `/user` | Sanctum | Returns authenticated user via `UserResource` |

## Conventions & Constraints

- **No repository.** Logic is thin enough to live directly in the controller using `User::query()`.
- Wrong credentials → `ValidationException::withMessages(['email' => [...]])`. Never return a generic 401 manually.
- `logout` calls `$user->currentAccessToken()->delete()` — deletes only the token used in the request, not all tokens for that user.
- Password is hashed automatically via `'password' => 'hashed'` in the User model casts. Never call `Hash::make()` manually before `create()`.
- Public routes (register, login) must remain **outside** the `auth:sanctum` middleware group in `routes/api.php`.

## Tests

```bash
php artisan test --compact tests/Feature/AuthTest.php
```
