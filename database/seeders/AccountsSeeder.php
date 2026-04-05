<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\PayableAccount\Models\PayableAccount;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            'Aluguel',
            'Celesc',
            'Internet Casa',
            'Unimed',
            'Uniodonto Conan',
            'Cartão Conan',
            'Cartão Emilly',
            'Transporte Conan',
            'Transporte Emilly',
            'Internet Cell Conan',
            'Internet Cell Emilly',
            'Comida / Flash',
            'Outros gastos Emilly',
        ];

        $accountData = array_map(fn ($name) => ['name' => $name], $accounts);

        $existingNames = PayableAccount::query()
            ->whereIn('name', $accounts)
            ->pluck('name')
            ->all();

        $newAccounts = array_filter($accountData, fn ($account) => !in_array($account['name'], $existingNames));

        if (!empty($newAccounts)) {
            PayableAccount::query()->insert($newAccounts);
        }
    }
}
