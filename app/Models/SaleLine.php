<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_sale_id',
        'product_id',
        'unit_price',
        'qty',
        'line_total',
    ];

    public function dailySale(): BelongsTo
    {
        return $this->belongsTo(DailySale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
