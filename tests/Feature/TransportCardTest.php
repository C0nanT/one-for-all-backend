<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Modules\TransportCard\Models\TransportCard;
use Modules\TransportCard\Models\TransportCardBalance;
use Modules\User\Models\User;

uses(RefreshDatabase::class);

// Testes desativados para evitar quebra da API, atenção para não criar mais testes dessa feature

// beforeEach(function (): void {
//     Config::set('tacom.base_url', 'https://tacom-test.example.com');
// });

// describe('CRUD', function (): void {
//     beforeEach(function (): void {
//         $this->user = User::factory()->create();
//         Sanctum::actingAs($this->user, ['*']);
//     });

//     test('index returns empty collection when no cards', function (): void {
//         $response = $this->getJson('/api/transport-cards');

//         $response->assertSuccessful()
//             ->assertJsonPath('data', []);
//     });

//     test('index returns transport cards', function (): void {
//         $card = TransportCard::factory()->create(['name' => 'My Card']);

//         $response = $this->getJson('/api/transport-cards');

//         $response->assertSuccessful()
//             ->assertJsonPath('data.0.id', $card->id)
//             ->assertJsonPath('data.0.name', 'My Card')
//             ->assertJsonPath('data.0.username', $card->username)
//             ->assertJsonPath('data.0.card_number', $card->card_number)
//             ->assertJsonPath('data.0.cpf', $card->cpf)
//             ->assertJsonMissingPath('data.0.password');
//     });

//     test('store creates transport card', function (): void {
//         $payload = [
//             'name' => 'New Card',
//             'username' => 'user1',
//             'password' => 'secret123',
//             'card_number' => '036200002681705',
//             'cpf' => '04357575125',
//         ];

//         $response = $this->postJson('/api/transport-cards', $payload);

//         $response->assertCreated()
//             ->assertJsonPath('data.name', 'New Card')
//             ->assertJsonPath('data.username', 'user1')
//             ->assertJsonPath('data.card_number', '036200002681705')
//             ->assertJsonPath('data.cpf', '04357575125')
//             ->assertJsonMissingPath('data.password');

//         $this->assertDatabaseHas('transport_cards', [
//             'name' => 'New Card',
//             'username' => 'user1',
//             'card_number' => '036200002681705',
//             'cpf' => '04357575125',
//         ]);
//     });

//     test('store validates required fields', function (): void {
//         $response = $this->postJson('/api/transport-cards', []);

//         $response->assertUnprocessable()
//             ->assertJsonValidationErrors(['name', 'username', 'password', 'card_number', 'cpf']);
//     });

//     test('show returns transport card', function (): void {
//         $card = TransportCard::factory()->create(['name' => 'Show Card']);

//         $response = $this->getJson("/api/transport-cards/{$card->id}");

//         $response->assertSuccessful()
//             ->assertJsonPath('data.id', $card->id)
//             ->assertJsonPath('data.name', 'Show Card')
//             ->assertJsonMissingPath('data.password');
//     });

//     test('show returns 404 when card not found', function (): void {
//         $response = $this->getJson('/api/transport-cards/99999');

//         $response->assertNotFound();
//     });

//     test('update modifies transport card', function (): void {
//         $card = TransportCard::factory()->create(['name' => 'Original']);

//         $response = $this->putJson("/api/transport-cards/{$card->id}", [
//             'name' => 'Updated Name',
//         ]);

//         $response->assertSuccessful()
//             ->assertJsonPath('data.name', 'Updated Name');

//         $this->assertDatabaseHas('transport_cards', [
//             'id' => $card->id,
//             'name' => 'Updated Name',
//         ]);
//     });

//     test('destroy deletes transport card', function (): void {
//         $card = TransportCard::factory()->create();

//         $response = $this->deleteJson("/api/transport-cards/{$card->id}");

//         $response->assertNoContent();
//         $this->assertDatabaseMissing('transport_cards', ['id' => $card->id]);
//     });
// });

// describe('balance and refresh when authenticated', function (): void {
//     beforeEach(function (): void {
//         $this->user = User::factory()->create();
//         Sanctum::actingAs($this->user, ['*']);
//         $this->card = TransportCard::factory()->create([
//             'username' => 'test-user',
//             'password' => 'test-pass',
//             'card_number' => '036200002681705',
//             'cpf' => '04357575125',
//         ]);
//     });

//     test('get balance returns from cache when record exists for today', function (): void {
//         TransportCardBalance::query()->create([
//             'transport_card_id' => $this->card->id,
//             'snapshot_date' => Carbon::today(),
//             'balance' => 50.00,
//             'card_number' => '03620000268170',
//             'raw_response' => null,
//         ]);

//         $response = $this->getJson("/api/transport-cards/{$this->card->id}/balance");

//         $response->assertSuccessful()
//             ->assertJsonPath('balance', 50)
//             ->assertJsonPath('from_cache', true)
//             ->assertJsonPath('card_number', '03620000268170')
//             ->assertJsonStructure(['balance', 'updated_at', 'from_cache', 'card_number', 'last_used_at', 'owner_name']);

//         Http::assertNothingSent();
//     });

//     test('get balance fetches from api when no cache for today', function (): void {
//         Http::fake(function (Request $request) {
//             if (str_contains($request->url(), 'auth2/login')) {
//                 return Http::response(['access_token' => 'fake-token']);
//             }
//             if (str_contains($request->url(), 'findCartao')) {
//                 return Http::response([
//                     'saldo' => 23.09,
//                     'codExternoCartao' => '03620000268170',
//                     'dataUsoCartao' => '2026-03-11T14:25:53.000+00:00',
//                     'dependenteTitular' => [
//                         'dependente' => [
//                             'nome' => 'CONAN CAMPOS SOUZA TORRES',
//                         ],
//                     ],
//                 ]);
//             }

//             return Http::response('Not found', 404);
//         });

//         $response = $this->getJson("/api/transport-cards/{$this->card->id}/balance");

//         $response->assertSuccessful()
//             ->assertJsonPath('balance', 23.09)
//             ->assertJsonPath('from_cache', false)
//             ->assertJsonPath('card_number', '03620000268170')
//             ->assertJsonPath('owner_name', 'CONAN CAMPOS SOUZA TORRES')
//             ->assertJsonStructure(['balance', 'updated_at', 'from_cache', 'card_number', 'last_used_at', 'owner_name']);

//         $this->assertDatabaseHas('transport_card_balances', [
//             'transport_card_id' => $this->card->id,
//             'balance' => 23.09,
//         ]);
//     });

//     test('get balance with refresh=1 forces api fetch even when cache exists', function (): void {
//         TransportCardBalance::query()->create([
//             'transport_card_id' => $this->card->id,
//             'snapshot_date' => Carbon::today(),
//             'balance' => 10.00,
//             'card_number' => '03620000268170',
//             'raw_response' => null,
//         ]);

//         Http::fake(function (Request $request) {
//             if (str_contains($request->url(), 'auth2/login')) {
//                 return Http::response(['access_token' => 'fake-token']);
//             }
//             if (str_contains($request->url(), 'findCartao')) {
//                 return Http::response([
//                     'saldo' => 99.50,
//                     'codExternoCartao' => '03620000268170',
//                     'dependenteTitular' => [
//                         'dependente' => [
//                             'nome' => 'CONAN CAMPOS SOUZA TORRES',
//                         ],
//                     ],
//                 ]);
//             }

//             return Http::response('Not found', 404);
//         });

//         $response = $this->getJson("/api/transport-cards/{$this->card->id}/balance?refresh=1");

//         $response->assertSuccessful()
//             ->assertJsonPath('balance', 99.50)
//             ->assertJsonPath('from_cache', false)
//             ->assertJsonPath('card_number', '03620000268170')
//             ->assertJsonPath('owner_name', 'CONAN CAMPOS SOUZA TORRES');

//         $this->assertDatabaseHas('transport_card_balances', [
//             'transport_card_id' => $this->card->id,
//             'balance' => 99.50,
//         ]);
//     });

//     test('post refresh always fetches from api and updates cache', function (): void {
//         TransportCardBalance::query()->create([
//             'transport_card_id' => $this->card->id,
//             'snapshot_date' => Carbon::today(),
//             'balance' => 5.00,
//             'card_number' => '03620000268170',
//             'raw_response' => null,
//         ]);

//         Http::fake(function (Request $request) {
//             if (str_contains($request->url(), 'auth2/login')) {
//                 return Http::response(['access_token' => 'fake-token']);
//             }
//             if (str_contains($request->url(), 'findCartao')) {
//                 return Http::response([
//                     'saldo' => 42.75,
//                     'codExternoCartao' => '03620000268170',
//                     'dependenteTitular' => [
//                         'dependente' => [
//                             'nome' => 'CONAN CAMPOS SOUZA TORRES',
//                         ],
//                     ],
//                 ]);
//             }

//             return Http::response('Not found', 404);
//         });

//         $response = $this->postJson("/api/transport-cards/{$this->card->id}/refresh");

//         $response->assertSuccessful()
//             ->assertJsonPath('balance', 42.75)
//             ->assertJsonPath('from_cache', false)
//             ->assertJsonPath('card_number', '03620000268170')
//             ->assertJsonPath('owner_name', 'CONAN CAMPOS SOUZA TORRES');

//         $this->assertDatabaseHas('transport_card_balances', [
//             'transport_card_id' => $this->card->id,
//             'balance' => 42.75,
//         ]);
//     });

//     test('balance returns 502 when tacom api fails', function (): void {
//         Http::fake([
//             '*' => Http::response('Server Error', 500),
//         ]);

//         $response = $this->getJson("/api/transport-cards/{$this->card->id}/balance");

//         $response->assertStatus(502)
//             ->assertJsonPath('message', 'Unable to fetch transport card balance. Please try again later.');
//     });

//     test('refresh returns 502 when tacom api fails', function (): void {
//         Http::fake([
//             '*' => Http::response('Server Error', 500),
//         ]);

//         $response = $this->postJson("/api/transport-cards/{$this->card->id}/refresh");

//         $response->assertStatus(502)
//             ->assertJsonPath('message', 'Unable to fetch transport card balance. Please try again later.');
//     });

//     test('balance returns 404 when card not found', function (): void {
//         $response = $this->getJson('/api/transport-cards/99999/balance');

//         $response->assertNotFound();
//     });
// });

// describe('when unauthenticated', function (): void {
//     test('index returns 401', function (): void {
//         $response = $this->getJson('/api/transport-cards');

//         $response->assertUnauthorized();
//     });

//     test('balance returns 401', function (): void {
//         $card = TransportCard::factory()->create();

//         $response = $this->getJson("/api/transport-cards/{$card->id}/balance");

//         $response->assertUnauthorized();
//     });
// });
