<?php

namespace App\Livewire\Accounting\Accounts;

use App\Exports\AccountsExport;
use App\Imports\AccountsImport;
use App\Livewire\Concerns\WithImport;
use App\Models\Accounting\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Chart of Accounts')]
class Index extends Component
{
    use WithPagination, WithImport;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getAccountsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

    public function delete(int $id): void
    {
        $account = Account::findOrFail($id);
        
        if ($account->is_system) {
            session()->flash('error', 'System accounts cannot be deleted.');
            return;
        }

        if ($account->journalLines()->exists()) {
            session()->flash('error', 'Cannot delete account with journal entries.');
            return;
        }

        $account->delete();
        session()->flash('success', 'Account deleted successfully.');
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new AccountsExport(), 'accounts-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new AccountsExport($this->selected), 'accounts-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getImportClass(): string
    {
        return AccountsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['code', 'name', 'type', 'parent_code', 'description', 'is_active'],
            'filename' => 'accounts-template.csv',
        ];
    }

    protected function getAccountsQuery()
    {
        return Account::query()
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%"))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->orderBy('code');
    }

    public function render()
    {
        $accounts = $this->getAccountsQuery()->paginate(20);

        return view('livewire.accounting.accounts.index', [
            'accounts' => $accounts,
        ]);
    }
}
