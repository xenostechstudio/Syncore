<?php

namespace App\Enums;

enum AccountType: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::ASSET => 'Asset',
            self::LIABILITY => 'Liability',
            self::EQUITY => 'Equity',
            self::REVENUE => 'Revenue',
            self::EXPENSE => 'Expense',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ASSET => 'blue',
            self::LIABILITY => 'red',
            self::EQUITY => 'violet',
            self::REVENUE => 'emerald',
            self::EXPENSE => 'amber',
        };
    }

    public function normalBalance(): string
    {
        return match ($this) {
            self::ASSET, self::EXPENSE => 'debit',
            self::LIABILITY, self::EQUITY, self::REVENUE => 'credit',
        };
    }

    public function isBalanceSheet(): bool
    {
        return in_array($this, [self::ASSET, self::LIABILITY, self::EQUITY]);
    }

    public function isIncomeStatement(): bool
    {
        return in_array($this, [self::REVENUE, self::EXPENSE]);
    }

    public static function balanceSheetTypes(): array
    {
        return [self::ASSET, self::LIABILITY, self::EQUITY];
    }

    public static function incomeStatementTypes(): array
    {
        return [self::REVENUE, self::EXPENSE];
    }
}
