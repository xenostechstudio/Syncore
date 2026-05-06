<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\FiscalPeriod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Accounting')]
class Index extends Component
{
    public function render()
    {
        // Account balances by type — single grouped scan, was 5 separate
        // SUM queries.
        $balancesByType = Account::query()
            ->selectRaw('type, SUM(balance) as total')
            ->groupBy('type')
            ->pluck('total', 'type');
        $totalAssets = (float) ($balancesByType['asset'] ?? 0);
        $totalLiabilities = (float) ($balancesByType['liability'] ?? 0);
        $totalEquity = (float) ($balancesByType['equity'] ?? 0);
        $totalRevenue = (float) ($balancesByType['revenue'] ?? 0);
        $totalExpenses = (float) ($balancesByType['expense'] ?? 0);

        $recentEntries = JournalEntry::with('createdBy')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('livewire.accounting.index', [
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $totalRevenue - $totalExpenses,
            'recentEntries' => $recentEntries,
            'currentPeriod' => FiscalPeriod::current(),
            'accountCount' => Account::where('is_active', true)->count(),
            'pendingEntries' => JournalEntry::where('status', 'draft')->count(),
        ]);
    }
}
