<?php

namespace App\Livewire\Accounting\Accounts;

use App\Models\Accounting\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Account')]
class Form extends Component
{
    public ?int $accountId = null;
    public ?Account $account = null;

    public string $code = '';
    public string $name = '';
    public string $accountType = 'asset';
    public ?int $parentId = null;
    public string $description = '';
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:accounts,code,' . $this->accountId,
            'name' => 'required|string|max:255',
            'accountType' => 'required|in:asset,liability,equity,revenue,expense',
            'parentId' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->accountId = $id;

        if ($id) {
            $this->account = Account::findOrFail($id);
            $this->code = $this->account->code;
            $this->name = $this->account->name;
            $this->accountType = $this->account->type;
            $this->parentId = $this->account->parent_id;
            $this->description = $this->account->description ?? '';
            $this->isActive = $this->account->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->accountType,
            'parent_id' => $this->parentId,
            'description' => $this->description ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->accountId) {
            $this->account->update($data);
            session()->flash('success', 'Account updated successfully.');
        } else {
            Account::create($data);
            session()->flash('success', 'Account created successfully.');
        }

        $this->redirect(route('accounting.accounts.index'), navigate: true);
    }

    public function delete(): void
    {
        if (!$this->account) return;

        if ($this->account->is_system) {
            session()->flash('error', 'System accounts cannot be deleted.');
            return;
        }

        if ($this->account->journalLines()->exists()) {
            session()->flash('error', 'Cannot delete account with journal entries.');
            return;
        }

        $this->account->delete();
        session()->flash('success', 'Account deleted successfully.');
        $this->redirect(route('accounting.accounts.index'), navigate: true);
    }

    public function render()
    {
        $parentAccounts = Account::where('is_active', true)
            ->whereNull('parent_id')
            ->when($this->accountId, fn ($q) => $q->where('id', '!=', $this->accountId))
            ->orderBy('code')
            ->get();

        return view('livewire.accounting.accounts.form', [
            'parentAccounts' => $parentAccounts,
        ]);
    }
}
