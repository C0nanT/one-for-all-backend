<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\TransportCard\Models\TransportCard;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\TransportCard\Models\TransportCard>
 */
class TransportCardFactory extends Factory
{
    protected $model = TransportCard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'card_number' => fake()->numerify('##############'),
            'cpf' => fake()->numerify('###########'),
        ];
    }
}
