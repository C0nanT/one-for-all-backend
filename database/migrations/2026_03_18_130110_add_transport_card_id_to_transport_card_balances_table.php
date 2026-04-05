<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transport_card_balances', function (Blueprint $table) {
            $table->foreignId('transport_card_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        $username = config('services.tacom.username');
        $password = config('services.tacom.password');
        $cardNumber = config('services.tacom.card_number');
        $cpf = config('services.tacom.cpf');

        if ($username && $password && $cardNumber && $cpf) {
            $id = DB::table('transport_cards')->insertGetId([
                'name' => 'Default',
                'username' => $username,
                'password' => encrypt($password),
                'card_number' => $cardNumber,
                'cpf' => $cpf,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('transport_card_balances')->update(['transport_card_id' => $id]);
        }

        Schema::table('transport_card_balances', function (Blueprint $table) {
            $table->dropUnique(['snapshot_date']);
            $table->unique(['transport_card_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_card_balances', function (Blueprint $table) {
            $table->dropUnique(['transport_card_id', 'snapshot_date']);
            $table->unique('snapshot_date');
        });

        Schema::table('transport_card_balances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transport_card_id');
        });
    }
};
