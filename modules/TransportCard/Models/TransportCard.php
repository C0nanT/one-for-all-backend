<?php

namespace Modules\TransportCard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportCard extends Model
{
    /** @use HasFactory<\Database\Factories\TransportCardFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'card_number',
        'cpf',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<\Modules\TransportCard\Models\TransportCard>
     */
    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return \Database\Factories\TransportCardFactory::new();
    }

    /**
     * @return HasMany<TransportCardBalance, $this>
     */
    public function balances(): HasMany
    {
        return $this->hasMany(TransportCardBalance::class);
    }
}
