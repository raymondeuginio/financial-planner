<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builders\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'category_id',
        'occurred_at',
        'description',
        'amount',
        'type',
        'notes',
    ];

    protected $casts = [
        'occurred_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['start_date'] ?? null, fn (Builder $q, $start) => $q->whereDate('occurred_at', '>=', $start))
            ->when($filters['end_date'] ?? null, fn (Builder $q, $end) => $q->whereDate('occurred_at', '<=', $end))
            ->when(($filters['type'] ?? 'all') !== 'all', function (Builder $q) use ($filters) {
                $q->where('type', $filters['type']);
            })
            ->when($filters['wallet_id'] ?? null, fn (Builder $q, $walletId) => $q->where('wallet_id', $walletId))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            });
    }
}
