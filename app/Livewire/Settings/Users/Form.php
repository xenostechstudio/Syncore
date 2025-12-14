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
    public string $phone = '';
    public ?string $out_of_office_date = null;
    public string $out_of_office_message = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $current_password = '';
    public bool $is_active = true;

    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    public array $activityLog = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->is_active = $user->email_verified_at !== null;

            $this->createdAt = $user->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $user->updated_at->format('M d, Y \a\t H:i');

            $this->activityLog = [
                [
                    'type' => 'created',
                    'message' => 'User created',
                    'user' => Auth::user()?->name ?? 'System',
                    'time' => $this->createdAt,
                ],
            ];

            if ($user->updated_at->gt($user->created_at)) {
                $this->activityLog[] = [
                    'type' => 'updated',
                    'message' => 'User updated',
                    'user' => Auth::user()?->name ?? 'System',
                    'time' => $this->updatedAt,
                ];
            }
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

        // Create flow: validate basic fields first, then prompt for password via modal.
        if ($this->userId === null && $this->password === '') {
            $this->validate($rules);
            $this->dispatch('open-change-password-modal');
            return;
        }

        if ($this->userId === null) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required_with:password', 'same:password'];
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
