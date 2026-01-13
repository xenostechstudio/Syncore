<?php

namespace App\Services;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;

/**
 * Accounting Service
 * 
 * Centralized business logic for accounting operations.
 * Handles journal entries, account balances, and financial reporting.
 * 
 * @package App\Services
 */
class AccountingService
{
    /**
     * Create a journal entry.
     *
     * @param array $data
     * @param array $lines
     * @return JournalEntry
     * @throws \Exception
     */
    public function createJournalEntry(array $data, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($data, $lines) {
            // Validate that debits equal credits
            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception('Journal entry must balance. Debits and credits must be equal.');
            }

            $journalEntry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $data['entry_date'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $lineData) {
                $journalEntry->lines()->create([
                    'account_id' => $lineData['account_id'],
                    'description' => $lineData['description'] ?? null,
                    'debit' => $lineData['debit'] ?? 0,
                    'credit' => $lineData['credit'] ?? 0,
                ]);
            }

            return $journalEntry->fresh(['lines.account']);
        });
    }

    /**
     * Post a journal entry.
     *
     * @param JournalEntry $journalEntry
     * @return bool
     */
    public function postJournalEntry(JournalEntry $journalEntry): bool
    {
        if ($journalEntry->status !== 'draft') {
            return false;
        }

        return DB::transaction(function () use ($journalEntry) {
            // Update account balances
            foreach ($journalEntry->lines as $line) {
                $account = $line->account;
                
                // For asset and expense accounts: debit increases, credit decreases
                // For liability, equity, and revenue accounts: credit increases, debit decreases
                if (in_array($account->type, ['asset', 'expense'])) {
                    $account->increment('balance', $line->debit - $line->credit);
                } else {
                    $account->increment('balance', $line->credit - $line->debit);
                }
            }

            $journalEntry->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            $journalEntry->logStatusChange('draft', 'posted', 'Journal entry posted');

            return true;
        });
    }

    /**
     * Reverse a posted journal entry.
     *
     * @param JournalEntry $journalEntry
     * @param string|null $reason
     * @return JournalEntry
     */
    public function reverseJournalEntry(JournalEntry $journalEntry, ?string $reason = null): JournalEntry
    {
        if ($journalEntry->status !== 'posted') {
            throw new \Exception('Can only reverse posted journal entries.');
        }

        return DB::transaction(function () use ($journalEntry, $reason) {
            // Create reversal entry
            $reversalEntry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => now(),
                'reference' => "Reversal of {$journalEntry->entry_number}",
                'description' => $reason ?? "Reversal of {$journalEntry->entry_number}",
                'status' => 'draft',
                'created_by' => auth()->id(),
                'reversed_entry_id' => $journalEntry->id,
            ]);

            // Create reversed lines (swap debit and credit)
            foreach ($journalEntry->lines as $line) {
                $reversalEntry->lines()->create([
                    'account_id' => $line->account_id,
                    'description' => "Reversal: {$line->description}",
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                ]);
            }

            // Mark original as reversed
            $journalEntry->update(['status' => 'reversed']);

            // Post the reversal
            $this->postJournalEntry($reversalEntry);

            return $reversalEntry->fresh(['lines.account']);
        });
    }

    /**
     * Get account balance.
     *
     * @param Account $account
     * @param string|null $asOfDate
     * @return float
     */
    public function getAccountBalance(Account $account, ?string $asOfDate = null): float
    {
        $query = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                $q->where('status', 'posted');
                if ($asOfDate) {
                    $q->whereDate('entry_date', '<=', $asOfDate);
                }
            });

        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        // Calculate balance based on account type
        if (in_array($account->type, ['asset', 'expense'])) {
            return $totalDebit - $totalCredit;
        }

        return $totalCredit - $totalDebit;
    }

    /**
     * Get trial balance.
     *
     * @param string|null $asOfDate
     * @return array
     */
    public function getTrialBalance(?string $asOfDate = null): array
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, $asOfDate);

            if ($balance == 0) {
                continue;
            }

            $debit = $balance > 0 && in_array($account->type, ['asset', 'expense']) ? $balance : 0;
            $credit = $balance > 0 && in_array($account->type, ['liability', 'equity', 'revenue']) ? $balance : 0;

            if ($balance < 0) {
                if (in_array($account->type, ['asset', 'expense'])) {
                    $credit = abs($balance);
                } else {
                    $debit = abs($balance);
                }
            }

            $trialBalance[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'debit' => $debit,
                'credit' => $credit,
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'accounts' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            'as_of_date' => $asOfDate ?? now()->toDateString(),
        ];
    }

    /**
     * Get income statement (P&L).
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getIncomeStatement(string $startDate, string $endDate): array
    {
        $revenueAccounts = Account::where('type', 'revenue')
            ->where('is_active', true)
            ->get();

        $expenseAccounts = Account::where('type', 'expense')
            ->where('is_active', true)
            ->get();

        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $balance = $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
            if ($balance != 0) {
                $revenues[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $balance,
                ];
                $totalRevenue += $balance;
            }
        }

        $expenses = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $account) {
            $balance = $this->getAccountBalanceForPeriod($account, $startDate, $endDate);
            if ($balance != 0) {
                $expenses[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $balance,
                ];
                $totalExpense += $balance;
            }
        }

        return [
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenses,
            'total_expense' => $totalExpense,
            'net_income' => $totalRevenue - $totalExpense,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Get account balance for a specific period.
     *
     * @param Account $account
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    protected function getAccountBalanceForPeriod(Account $account, string $startDate, string $endDate): float
    {
        $query = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereDate('entry_date', '>=', $startDate)
                    ->whereDate('entry_date', '<=', $endDate);
            });

        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        if (in_array($account->type, ['asset', 'expense'])) {
            return $totalDebit - $totalCredit;
        }

        return $totalCredit - $totalDebit;
    }
}
