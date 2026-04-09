# TransportCard Module

## Purpose

Stores transport card credentials and fetches the current balance from the external Tacom API. Balances are cached per day in `TransportCardBalance` to avoid redundant external calls.

## Key Files

```
Models/
  TransportCard.php         ← name, username, password (cast: 'encrypted'), card_number, cpf
  TransportCardBalance.php  ← balance (float), card_number, raw_response (JSON), date

Services/
  TacomApiService.php       ← login(username, password) → token; findCartao(token, cardNumber, cpf) → array
  TransportCardService.php  ← getBalance(card, forceRefresh): cache check → Tacom call → upsert

Repositories/
  TransportCardBalanceRepository.php  ← getForDate(cardId, date), upsertForDate(...)
  (interface in Contracts/Repositories/ — bound in AppServiceProvider)

Http/Controllers/TransportCardController.php
Http/Requests/StoreTransportCardRequest.php
Http/Requests/UpdateTransportCardRequest.php
routes/api.php
config/tacom.php   ← base_url, auth_path, find_cartao_path (read from env)
```

## API Endpoints

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/transport-cards` | List all cards |
| POST | `/transport-cards` | Create card |
| GET | `/transport-cards/{id}` | Get single card |
| PUT | `/transport-cards/{id}` | Update card |
| DELETE | `/transport-cards/{id}` | Delete card |
| GET | `/transport-cards/{id}/balance` | Cached balance (today's record if exists) |
| POST | `/transport-cards/{id}/refresh` | Force-refresh from Tacom API |

## Tacom API Flow

1. `TacomApiService::login(username, password)` → POST to `tacom.base_url + tacom.auth_path`, returns `access_token`
2. `TacomApiService::findCartao(token, cardNumber, cpf)` → GET `base_url + tacom.find_cartao_path/{cardNumber}/0/{cpf}`
3. Balance is in `$data['saldo']`. Owner name: `$data['dependenteTitular']['dependente']['nome']`. Last used: `$data['dataUsoCartao']`.
4. Full raw response is stored in `TransportCardBalance.raw_response` as JSON.

## Caching Logic

`TransportCardService::getBalance($card, $forceRefresh = false)`:
- Without `$forceRefresh`: checks `repository->getForDate($card->id, Carbon::today())` first.
- If found → returns it with `from_cache: true`.
- Otherwise → calls Tacom API, upserts record for today, returns with `from_cache: false`.

## Pitfalls

- **`password` is cast `'encrypted'`** — Laravel encrypts/decrypts transparently. Never log or expose it in API responses; the resource must omit it.
- **Never call `TacomApiService` directly in tests** — mock it with `$this->mock(TacomApiService::class, ...)` to avoid real HTTP calls and flaky tests.
- **`TacomApiService` throws `\RuntimeException` on any API error** — let it bubble to the exception handler (becomes a 500). Do not catch it in the controller.
- `upsertForDate` ensures one balance record per card per day — do not create balance records manually.

## Tests

```bash
php artisan test --compact tests/Feature/TransportCardTest.php
```
