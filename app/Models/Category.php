<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';   // Uang Masuk
    public const TYPE_OUT = 'out'; // Uang Keluar

    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * Get transactions in this category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope query to income categories only (Uang Masuk).
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IN);
    }

    /**
     * Scope query to expense categories only (Uang Keluar).
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /**
     * Get human-friendly label for category type.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_IN ? 'Uang Masuk' : 'Uang Keluar';
    }
}
