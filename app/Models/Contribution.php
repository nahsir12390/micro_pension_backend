<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pension_plan_id',
        'amount',
        'payment_method',
        'status',
        'reference',
        'contributed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'contributed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pensionPlan(): BelongsTo
    {
        return $this->belongsTo(PensionPlan::class);
    }
}
