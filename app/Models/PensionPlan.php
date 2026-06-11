<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PensionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'minimum_amount',
        'frequency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }
}
