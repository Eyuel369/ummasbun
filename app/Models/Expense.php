<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Ingredients',
        'Utilities',
        'Salary',
        'Rent',
        'Transport',
        'Maintenance',
        'Other',
    ];

    protected $fillable = [
        'expense_date',
        'category',
        'amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
