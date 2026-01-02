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
        $totalAssets = Account::where('type', 'asset')->sum('balance');
        $totalLiabilities = Account::where('type', 'liability')->sum('balance');
        $totalEquity = Account::where('type', 'equity')->sum('balance');
        $totalRevenue = Account::where('type', 'revenue')->sum('balance');
        $totalExpenses = Account::where('type', 'expense')->sum('balance');

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
