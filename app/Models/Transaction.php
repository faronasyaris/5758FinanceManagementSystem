<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';       // Uang Masuk
    public const TYPE_OUT = 'out';     // Uang Keluar

    public const STATUS_PAID = 'paid';       // Lunas / Cair
    public const STATUS_PENDING = 'pending'; // Belum Cair / Piutang / Kasbon

    protected $fillable = [
        'project_id',
        'category_id',
        'account_id',
        'type',
        'nominal',
        'transaction_date',
        'due_date',
        'payment_status',
        'parent_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'transaction_date' => 'date',
            'due_date' => 'date',
        ];
    }

    /**
     * The project this transaction belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The category of this transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The account (kas / bank) destination or source.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * The parent transaction (if this is an installment settlement or partial payment).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_id');
    }

    /**
     * Child transactions related to this transaction.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_id');
    }

    /**
     * Scope for income transactions (Uang Masuk).
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IN);
    }

    /**
     * Scope for expense transactions (Uang Keluar).
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /**
     * Scope for paid / cair transactions.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PAID);
    }

    /**
     * Scope for pending transactions (Piutang / Hutang / Kasbon).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    /**
     * Scope for overdue pending transactions (Jatuh tempo terlewati).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today()->toDateString());
    }

    /**
     * Check if this transaction is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->payment_status === self::STATUS_PENDING
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /**
     * Jargon-free label for transaction type.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_IN ? 'Uang Masuk' : 'Uang Keluar';
    }

    /**
     * Jargon-free label for payment status.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return $this->payment_status === self::STATUS_PAID ? 'Lunas / Cair' : 'Belum Cair (Pending)';
    }
}
