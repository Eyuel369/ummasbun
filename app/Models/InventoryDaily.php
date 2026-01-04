<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDaily extends Model
{
    use HasFactory;

    protected $table = 'inventory_daily';

    protected $fillable = [
        'inv_date',
        'inventory_item_id',
        'yesterday_remaining',
        'stock_in',
        'today_remaining',
        'auto_usage',
        'created_by',
    ];

    protected $casts = [
        'inv_date' => 'date',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
