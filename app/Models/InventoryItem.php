<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $casts = [
        'active' => 'boolean',
        'min_level' => 'decimal:2',
    ];

    protected $fillable = [
        'name',
        'unit',
        'min_level',
        'active',
    ];

    public function inventoryDailies(): HasMany
    {
        return $this->hasMany(InventoryDaily::class);
    }
}
