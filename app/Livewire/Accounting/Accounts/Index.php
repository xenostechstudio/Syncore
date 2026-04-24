<?php

namespace App\Livewire\Accounting\Accounts;

use App\Exports\AccountsExport;
use App\Imports\AccountsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Accounting\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Chart of Accounts')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    #[Url]
    public string $type = '';

    public function updatedType(): void
    {
        $this->resetPage();
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
        $filename = empty($this->selected)
            ? 'accounts-' . now()->format('Y-m-d') . '.xlsx'
            : 'accounts-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AccountsExport($this->selected ?: null), $filename);
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

    protected function getQuery()
    {
        return Account::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->type, fn ($q) => $q->where('type', $this->type));
    }

    protected function getModelClass(): string
    {
        return Account::class;
    }

    public function render()
    {
        $accounts = $this->getQuery()
            ->orderBy('code')
            ->paginate(20, ['*'], 'page', $this->page);

        return view('livewire.accounting.accounts.index', [
            'accounts' => $accounts,
        ]);
    }
}
