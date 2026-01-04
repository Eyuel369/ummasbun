<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_sale_id',
        'method',
        'amount',
        'customer_name',
    ];

    public function dailySale(): BelongsTo
    {
        return $this->belongsTo(DailySale::class);
    }
}
