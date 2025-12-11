<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('User')]
class Form extends Component
{
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public bool $is_active = true;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->is_active = $user->email_verified_at !== null;
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($this->userId),
            ],
        ];

        if ($this->userId === null) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $this->validate($rules);

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
        } else {
            $user = new User();
        }

        $user->name = $this->name;
        $user->email = $this->email;

        if ($this->password !== '') {
            $user->password = $this->password;
        }

        if ($this->is_active) {
            if ($user->email_verified_at === null) {
                $user->email_verified_at = now();
            }
        } else {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->userId = $user->id;

        session()->flash('success', 'User saved successfully.');

        $this->redirect(route('settings.users.edit', $user->id), navigate: true);
    }

    public function delete(): void
    {
        if ($this->userId === null) {
            return;
        }

        if (Auth::id() === $this->userId) {
            session()->flash('error', 'You cannot delete your own user account.');
            return;
        }

        $user = User::findOrFail($this->userId);
        $user->delete();

        session()->flash('success', 'User deleted successfully.');

        $this->redirect(route('settings.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.users.form');
    }
}
