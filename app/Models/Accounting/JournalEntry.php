<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'fiscal_period_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $prefix = "JE/{$year}/";
        $last = self::where('entry_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();
        $next = $last ? ((int) substr($last->entry_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function post(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        // Validate balanced
        if (abs($this->total_debit - $this->total_credit) > 0.01) {
            return false;
        }

        $this->update(['status' => 'posted']);

        // Update account balances
        foreach ($this->lines as $line) {
            $line->account->recalculateBalance();
        }

        return true;
    }

    public function recalculateTotals(): void
    {
        $this->total_debit = $this->lines()->sum('debit');
        $this->total_credit = $this->lines()->sum('credit');
        $this->save();
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}
