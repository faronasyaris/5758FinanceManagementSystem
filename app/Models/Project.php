<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'client_id',
        'name',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the client that owns the project.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all transactions for this project.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the projections for this project.
     */
    public function projections(): HasMany
    {
        return $this->hasMany(Projection::class);
    }

    /**
     * Get the latest projection for this project.
     */
    public function projection(): HasOne
    {
        return $this->hasOne(Projection::class)->latestOfMany();
    }

    /**
     * Scope query to ongoing projects.
     */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    /**
     * Scope query to draft projects.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope query to completed projects.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Total Income: (Paid Income + Pending Income).
     */
    public function getTotalIncomeAttribute(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_IN)
            ->sum('nominal');
    }

    /**
     * Total Expense: (Paid Expense + Pending Expense).
     */
    public function getTotalExpenseAttribute(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_OUT)
            ->sum('nominal');
    }

    /**
     * Project Margin: (Total Paid Income + Pending Income) - (Total Paid Expense + Pending Expense).
     */
    public function getProjectMarginAttribute(): float
    {
        return $this->total_income - $this->total_expense;
    }

    /**
     * Realized (Cair / Lunas) Income.
     */
    public function getRealizedIncomeAttribute(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_IN)
            ->where('payment_status', Transaction::STATUS_PAID)
            ->sum('nominal');
    }

    /**
     * Realized (Sudah Dibayar) Expense.
     */
    public function getRealizedExpenseAttribute(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_OUT)
            ->where('payment_status', Transaction::STATUS_PAID)
            ->sum('nominal');
    }

    /**
     * Realized Margin.
     */
    public function getRealizedMarginAttribute(): float
    {
        return $this->realized_income - $this->realized_expense;
    }

    /**
     * Pending Income (Piutang Termin).
     */
    public function getPendingIncomeAttribute(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_IN)
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->sum('nominal');
    }

    /**
     * Pending Expense (Hutang / Kasbon).
     */
    public function getPendingExpenseAttribute(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_OUT)
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->sum('nominal');
    }

    /**
     * Check if project is profitable.
     */
    public function isProfitable(): bool
    {
        return $this->project_margin >= 0;
    }
}
