<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'initial_balance',
        'current_balance',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
        ];
    }

    /**
     * Get all transactions settled into or paid from this account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Recalculate and persist the current balance based on initial balance and paid transactions.
     */
    public function recalculateBalance(): void
    {
        $paidIncome = $this->transactions()
            ->where('payment_status', Transaction::STATUS_PAID)
            ->where('type', Transaction::TYPE_IN)
            ->sum('nominal');

        $paidExpense = $this->transactions()
            ->where('payment_status', Transaction::STATUS_PAID)
            ->where('type', Transaction::TYPE_OUT)
            ->sum('nominal');

        $this->current_balance = (float) $this->initial_balance + (float) $paidIncome - (float) $paidExpense;
        $this->save();
    }
}
