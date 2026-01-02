<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'description',
        'balance',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expense',
            default => $this->type,
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->type) {
            'asset' => 'blue',
            'liability' => 'red',
            'equity' => 'purple',
            'revenue' => 'emerald',
            'expense' => 'amber',
            default => 'zinc',
        };
    }

    public function recalculateBalance(): void
    {
        $debits = $this->journalLines()
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->sum('debit');
        
        $credits = $this->journalLines()
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->sum('credit');

        // For asset/expense: balance = debits - credits
        // For liability/equity/revenue: balance = credits - debits
        $this->balance = in_array($this->type, ['asset', 'expense'])
            ? $debits - $credits
            : $credits - $debits;
        
        $this->save();
    }
}
