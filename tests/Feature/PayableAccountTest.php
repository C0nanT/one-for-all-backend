<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\PayableAccount\Models\PayableAccount;
use Modules\PayableAccount\Models\PayableAccountPayment;
use Modules\User\Models\User;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user, ['*']);
});

test('index returns all payable accounts', function (): void {
    PayableAccount::factory()->count(2)->create();

    $response = $this->getJson('/api/payable-accounts?period=2026-01');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'status', 'payment', 'created_at', 'updated_at'],
            ],
            'summary' => ['period', 'month_total', 'paid_by_user'],
        ]);
});

test('index returns empty when there are no accounts', function (): void {
    $response = $this->getJson('/api/payable-accounts?period=2026-01');

    $response->assertSuccessful()
        ->assertJsonPath('data', [])
        ->assertJsonPath('summary.month_total', 0)
        ->assertJsonPath('summary.paid_by_user', []);
});

test('index filters payments by period when period query param is passed', function (): void {
    $account = PayableAccount::factory()->create(['name' => 'Account with payments']);
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account->id,
        'amount' => 100,
        'payer_id' => $this->user->id,
        'period' => '2026-01-01',
    ]);
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account->id,
        'amount' => 200,
        'payer_id' => $this->user->id,
        'period' => '2026-02-01',
    ]);

    $response = $this->getJson('/api/payable-accounts?period=2026-02');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Account with payments')
        ->assertJsonPath('data.0.payment.period', '01-02-2026');
    expect((float) $response->json('data.0.payment.amount'))->toBe(200.0);
});

test('index returns status paid_zero when payment amount is zero', function (): void {
    $account = PayableAccount::factory()->create(['name' => 'Zero amount account']);
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account->id,
        'amount' => 0,
        'payer_id' => $this->user->id,
        'period' => '2026-01-01',
    ]);

    $response = $this->getJson('/api/payable-accounts?period=2026-01');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.status', 'paid_zero');
    expect((float) $response->json('data.0.payment.amount'))->toBe(0.0);
});

test('index returns payment for period when period query param is passed', function (): void {
    $account = PayableAccount::factory()->create();
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account->id,
        'amount' => 100,
        'payer_id' => $this->user->id,
        'period' => '2026-01-01',
    ]);
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account->id,
        'amount' => 200,
        'payer_id' => $this->user->id,
        'period' => '2026-02-01',
    ]);

    $response = $this->getJson('/api/payable-accounts?period=2026-02');

    $response->assertSuccessful();
    expect((float) $response->json('data.0.payment.amount'))->toBe(200.0);
});

test('index returns summary with month_total and paid_by_user', function (): void {
    $payer1 = User::factory()->create(['name' => 'Alice']);
    $payer2 = User::factory()->create(['name' => 'Bob']);

    $account1 = PayableAccount::factory()->create();
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account1->id,
        'amount' => 150.50,
        'payer_id' => $payer1->id,
        'period' => '2026-02-01',
    ]);

    $account2 = PayableAccount::factory()->create();
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account2->id,
        'amount' => 299.50,
        'payer_id' => $payer2->id,
        'period' => '2026-02-01',
    ]);

    $response = $this->getJson('/api/payable-accounts?period=2026-02');

    $response->assertSuccessful()
        ->assertJsonPath('summary.period', '2026-02')
        ->assertJsonPath('summary.month_total', 450)
        ->assertJsonCount(2, 'summary.paid_by_user');

    $paidByUser = $response->json('summary.paid_by_user');
    expect($paidByUser)->toBeArray();

    $alice = collect($paidByUser)->firstWhere('user_id', $payer1->id);
    $bob = collect($paidByUser)->firstWhere('user_id', $payer2->id);
    expect($alice)->toMatchArray(['user_id' => $payer1->id, 'name' => 'Alice', 'total_paid' => 150.50])
        ->and($bob)->toMatchArray(['user_id' => $payer2->id, 'name' => 'Bob', 'total_paid' => 299.50]);
});

test('index summary aggregates multiple payments by same payer', function (): void {
    $payer = User::factory()->create(['name' => 'Carol']);

    $account1 = PayableAccount::factory()->create();
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account1->id,
        'amount' => 100,
        'payer_id' => $payer->id,
        'period' => '2026-02-01',
    ]);

    $account2 = PayableAccount::factory()->create();
    PayableAccountPayment::query()->create([
        'payable_account_id' => $account2->id,
        'amount' => 50,
        'payer_id' => $payer->id,
        'period' => '2026-02-01',
    ]);

    $response = $this->getJson('/api/payable-accounts?period=2026-02');

    $response->assertSuccessful()
        ->assertJsonPath('summary.month_total', 150)
        ->assertJsonCount(1, 'summary.paid_by_user')
        ->assertJsonPath('summary.paid_by_user.0', [
            'user_id' => $payer->id,
            'name' => 'Carol',
            'total_paid' => 150,
        ]);
});

test('index summary returns zero month_total and empty paid_by_user when no payments', function (): void {
    PayableAccount::factory()->count(2)->create();

    $response = $this->getJson('/api/payable-accounts?period=2026-01');

    $response->assertSuccessful()
        ->assertJsonPath('summary.period', '2026-01')
        ->assertJsonPath('summary.month_total', 0)
        ->assertJsonPath('summary.paid_by_user', []);
});

test('index returns 422 when period query param is invalid', function (): void {
    $response = $this->getJson('/api/payable-accounts?period=invalid');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['period']);
});

test('can create payable account with valid data', function (): void {
    $response = $this->postJson('/api/payable-accounts', [
        'name' => 'Supplier XYZ',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Supplier XYZ');

    $this->assertDatabaseHas('payable_accounts', [
        'name' => 'Supplier XYZ',
    ]);
});

test('creation fails without name', function (): void {
    $response = $this->postJson('/api/payable-accounts', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('shows a payable account', function (): void {
    $payableAccount = PayableAccount::factory()->create(['name' => 'Unique account']);

    $response = $this->getJson("/api/payable-accounts/{$payableAccount->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $payableAccount->id)
        ->assertJsonPath('data.name', 'Unique account');
});

test('show returns 404 for non-existent account', function (): void {
    $response = $this->getJson('/api/payable-accounts/99999');

    $response->assertNotFound()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.error', 'Resource not found.');
});

test('can update payable account', function (): void {
    $payableAccount = PayableAccount::factory()->create([
        'name' => 'Before',
    ]);

    $response = $this->putJson("/api/payable-accounts/{$payableAccount->id}", [
        'name' => 'After',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'After');

    $payableAccount->refresh();
    expect($payableAccount->name)->toBe('After');
});

test('can update partially', function (): void {
    $payableAccount = PayableAccount::factory()->create([
        'name' => 'Original',
    ]);

    $response = $this->patchJson("/api/payable-accounts/{$payableAccount->id}", [
        'name' => 'Updated',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated');
});

test('can delete payable account', function (): void {
    $payableAccount = PayableAccount::factory()->create();

    $response = $this->deleteJson("/api/payable-accounts/{$payableAccount->id}");

    $response->assertNoContent();
    $this->assertSoftDeleted('payable_accounts', ['id' => $payableAccount->id]);
});

test('index orders unpaid accounts first then alphabetically', function (): void {
    $paidAccount = PayableAccount::factory()->create(['name' => 'Alpha']);
    PayableAccount::factory()->create(['name' => 'Beta']);

    PayableAccountPayment::query()->create([
        'payable_account_id' => $paidAccount->id,
        'amount' => 100,
        'payer_id' => $this->user->id,
        'period' => '2026-03-01',
    ]);

    $response = $this->getJson('/api/payable-accounts?period=2026-03');

    $response->assertSuccessful();
    expect($response->json('data.0.name'))->toBe('Beta')
        ->and($response->json('data.1.name'))->toBe('Alpha');
});

test('index orders alphabetically within the same status', function (): void {
    PayableAccount::factory()->create(['name' => 'Zebra']);
    PayableAccount::factory()->create(['name' => 'Apple']);
    PayableAccount::factory()->create(['name' => 'Mango']);

    $response = $this->getJson('/api/payable-accounts?period=2026-03');

    $response->assertSuccessful();
    expect($response->json('data.0.name'))->toBe('Apple')
        ->and($response->json('data.1.name'))->toBe('Mango')
        ->and($response->json('data.2.name'))->toBe('Zebra');
});
