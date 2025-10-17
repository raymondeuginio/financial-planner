<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'month',
        'amount',
    ];

    protected $casts = [
        'month' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
