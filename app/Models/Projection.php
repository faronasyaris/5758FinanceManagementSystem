<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Projection extends Model
{
    use HasFactory;

    public const MARGIN_TYPE_PERCENTAGE = 'percentage';
    public const MARGIN_TYPE_NOMINAL = 'nominal';

    protected $fillable = [
        'project_id',
        'total_contract_value',
        'target_margin_type',
        'target_margin_value',
        'estimated_expense',
        'period_month',
        'period_year',
    ];

    protected function casts(): array
    {
        return [
            'total_contract_value' => 'decimal:2',
            'target_margin_value' => 'decimal:2',
            'estimated_expense' => 'decimal:2',
            'period_month' => 'integer',
            'period_year' => 'integer',
        ];
    }

    /**
     * Get the project associated with this projection.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate estimated maximum expense (Pagu Anggaran) based on contract value and target margin.
     *
     * @param float $contractValue
     * @param string $marginType ('percentage' | 'nominal')
     * @param float $marginValue
     * @return float
     */
    public static function calculateEstimatedExpense(float $contractValue, string $marginType, float $marginValue): float
    {
        if ($marginType === self::MARGIN_TYPE_PERCENTAGE) {
            $profitTarget = $contractValue * ($marginValue / 100);
        } else {
            $profitTarget = $marginValue;
        }

        return max(0, $contractValue - $profitTarget);
    }

    /**
     * Target profit in nominal Rupiah.
     */
    public function getTargetProfitNominalAttribute(): float
    {
        if ($this->target_margin_type === self::MARGIN_TYPE_PERCENTAGE) {
            return (float) $this->total_contract_value * ((float) $this->target_margin_value / 100);
        }

        return (float) $this->target_margin_value;
    }

    /**
     * Target profit in percentage.
     */
    public function getTargetProfitPercentageAttribute(): float
    {
        if ($this->target_margin_type === self::MARGIN_TYPE_PERCENTAGE) {
            return (float) $this->target_margin_value;
        }

        if ((float) $this->total_contract_value > 0) {
            return ((float) $this->target_margin_value / (float) $this->total_contract_value) * 100;
        }

        return 0;
    }
}
