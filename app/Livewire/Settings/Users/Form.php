<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('User')]
class Form extends Component
{
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

    // Role assignment (single role)
    public ?string $selectedRole = null;

    // Calendar fields
    public string $working_hours_start = '09:00';
    public string $working_hours_end = '17:00';
    public array $working_days = ['mon', 'tue', 'wed', 'thu', 'fri'];
    public ?string $out_of_office_start = null;
    public ?string $out_of_office_end = null;
    public string $out_of_office_message = '';

    // Localization
    public string $language = 'id';
    public string $timezone = 'Asia/Jakarta';
    public string $signature = '';

    // Security
    public bool $twoFactorEnabled = false;
    public array $sessions = [];

    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->is_active = $user->email_verified_at !== null;
            $this->twoFactorEnabled = $user->two_factor_secret !== null && $user->two_factor_confirmed_at !== null;
            $this->selectedRole = $user->roles->first()?->name;

            // Calendar fields
            $this->working_hours_start = $user->working_hours_start ?? '09:00';
            $this->working_hours_end = $user->working_hours_end ?? '17:00';
            $this->working_days = $user->working_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];
            $this->out_of_office_start = $user->out_of_office_start?->format('Y-m-d');
            $this->out_of_office_end = $user->out_of_office_end?->format('Y-m-d');
            $this->out_of_office_message = $user->out_of_office_message ?? '';

            // Localization
            $this->language = $user->language ?? 'id';
            $this->timezone = $user->timezone ?? 'Asia/Jakarta';
            $this->signature = $user->signature ?? '';

            $this->createdAt = $user->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $user->updated_at->format('M d, Y \a\t H:i');

            $this->loadSessions();
        }
    }

    public function getActivities(): \Illuminate\Support\Collection
    {
        if (!$this->userId) {
            return collect();
        }

        return \Spatie\Activitylog\Models\Activity::where('subject_type', User::class)
            ->where('subject_id', $this->userId)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();
    }

    public function getAvailableRoles(): Collection
    {
        if (!class_exists('\Spatie\Permission\Models\Role')) {
            return collect();
        }

        return \Spatie\Permission\Models\Role::orderBy('name')->get();
    }

    protected function loadSessions(): void
    {
        if (!$this->userId) {
            $this->sessions = [];
            return;
        }

        $currentSessionId = session()->getId();
        $sessions = DB::table('sessions')
            ->where('user_id', $this->userId)
            ->orderBy('last_activity', 'desc')
            ->get();

        $this->sessions = $sessions->map(function ($session) use ($currentSessionId) {
            $userAgent = $session->user_agent ?? '';
            $parsed = $this->parseUserAgent($userAgent);

            $lastActive = \Carbon\Carbon::createFromTimestamp($session->last_activity);
            $isNow = $lastActive->diffInMinutes(now()) < 5;

            return [
                'id' => $session->id,
                'device' => $parsed['device'],
                'browser' => $parsed['browser'] . ' on ' . $parsed['platform'],
                'ip' => $session->ip_address ?? 'Unknown',
                'last_active' => $isNow ? 'Now' : $lastActive->diffForHumans(),
                'is_current' => $session->id === $currentSessionId,
                'is_mobile' => $parsed['is_mobile'],
            ];
        })->toArray();
    }

    protected function parseUserAgent(string $userAgent): array
    {
        $device = 'Unknown Device';
        $browser = 'Unknown Browser';
        $platform = 'Unknown';
        $isMobile = false;

        // Detect platform
        if (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $platform = 'iOS';
            $device = 'iPhone';
            $isMobile = true;
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $platform = 'iOS';
            $device = 'iPad';
            $isMobile = true;
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
            $isMobile = true;
            if (preg_match('/Mobile/i', $userAgent)) {
                $device = 'Android Phone';
            } else {
                $device = 'Android Tablet';
            }
        }

        // Detect browser
        if (preg_match('/Chrome\/[\d.]+/i', $userAgent) && !preg_match('/Edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edg\/[\d.]+/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        }

        if ($device === 'Unknown Device') {
            $device = $platform;
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'platform' => $platform,
            'is_mobile' => $isMobile,
        ];
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
            'phone' => ['nullable', 'string', 'max:30'],
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
        $user->phone = $this->phone ?: null;

        // Calendar fields
        $user->working_hours_start = $this->working_hours_start;
        $user->working_hours_end = $this->working_hours_end;
        $user->working_days = $this->working_days;
        $user->out_of_office_start = $this->out_of_office_start ?: null;
        $user->out_of_office_end = $this->out_of_office_end ?: null;
        $user->out_of_office_message = $this->out_of_office_message ?: null;

        // Localization
        $user->language = $this->language;
        $user->timezone = $this->timezone;
        $user->signature = $this->signature ?: null;

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

        // Sync role (single role) and log the change
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $oldRoles = $user->roles->pluck('name')->toArray();
            $newRoles = $this->selectedRole ? [$this->selectedRole] : [];
            
            $user->syncRoles($newRoles);
            
            // Log role changes
            if ($oldRoles !== $newRoles) {
                $addedRoles = array_diff($newRoles, $oldRoles);
                $removedRoles = array_diff($oldRoles, $newRoles);
                
                if (!empty($addedRoles)) {
                    activity()
                        ->performedOn($user)
                        ->causedBy(Auth::user())
                        ->withProperties(['role' => implode(', ', $addedRoles)])
                        ->log(__('activity.role_assigned', ['role' => implode(', ', $addedRoles)]));
                }
                
                if (!empty($removedRoles)) {
                    activity()
                        ->performedOn($user)
                        ->causedBy(Auth::user())
                        ->withProperties(['role' => implode(', ', $removedRoles)])
                        ->log(__('activity.role_removed', ['role' => implode(', ', $removedRoles)]));
                }
            }
        }

        $this->userId = $user->id;
        $this->password = '';
        $this->password_confirmation = '';

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

    public function enableTwoFactor(): void
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            session()->flash('error', 'You can only enable 2FA for your own account.');
            return;
        }

        $user = User::findOrFail($this->userId);

        // Generate secret using Fortify's provider
        $provider = app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
        
        $user->forceFill([
            'two_factor_secret' => encrypt($provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 8))->map(fn () => \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10))->all())),
        ])->save();

        $this->twoFactorEnabled = false; // Not confirmed yet
        
        // Dispatch event to show QR code modal
        $this->dispatch('show-two-factor-qr-modal');
    }

    public function confirmTwoFactor(string $code): void
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            session()->flash('error', 'You can only confirm 2FA for your own account.');
            return;
        }

        $user = User::findOrFail($this->userId);

        if (!$user->two_factor_secret) {
            session()->flash('error', 'Two-factor authentication has not been enabled.');
            return;
        }

        $provider = app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
        $secret = decrypt($user->two_factor_secret);

        if (!$provider->verify($secret, $code)) {
            session()->flash('error', 'The provided two-factor authentication code was invalid.');
            return;
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->twoFactorEnabled = true;
        session()->flash('success', 'Two-factor authentication has been enabled.');
    }

    public function getTwoFactorQrCodeUrl(): ?string
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            return null;
        }

        $user = User::findOrFail($this->userId);

        if (!$user->two_factor_secret) {
            return null;
        }

        $provider = app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
        $secret = decrypt($user->two_factor_secret);
        
        return $provider->qrCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    public function getTwoFactorSecret(): ?string
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            return null;
        }

        $user = User::findOrFail($this->userId);

        if (!$user->two_factor_secret) {
            return null;
        }

        return decrypt($user->two_factor_secret);
    }

    public function cancelTwoFactorSetup(): void
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            return;
        }

        $user = User::findOrFail($this->userId);

        // Only cancel if not yet confirmed
        if ($user->two_factor_confirmed_at === null) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ])->save();
        }
    }

    public function resetOutOfOffice(): void
    {
        $this->out_of_office_start = null;
        $this->out_of_office_end = null;
        $this->out_of_office_message = '';
    }

    public function toggleWorkingDay(string $day): void
    {
        if (in_array($day, $this->working_days)) {
            $this->working_days = array_values(array_diff($this->working_days, [$day]));
        } else {
            $this->working_days[] = $day;
        }
    }

    public function disableTwoFactor(): void
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            session()->flash('error', 'You can only disable 2FA for your own account.');
            return;
        }

        $user = User::findOrFail($this->userId);
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $this->twoFactorEnabled = false;
        session()->flash('success', 'Two-factor authentication has been disabled.');
    }

    public function showRecoveryCodes(): void
    {
        if (!$this->userId || $this->userId !== Auth::id()) {
            session()->flash('error', 'You can only view recovery codes for your own account.');
            return;
        }

        session()->flash('info', 'Recovery codes feature - implement dedicated view.');
    }

    public function revokeSession(string $sessionId): void
    {
        if (!$this->userId) {
            return;
        }

        // Don't allow revoking current session
        if ($sessionId === session()->getId()) {
            session()->flash('error', 'You cannot revoke your current session.');
            return;
        }

        DB::table('sessions')->where('id', $sessionId)->where('user_id', $this->userId)->delete();
        
        $this->loadSessions();
        session()->flash('success', 'Session revoked successfully.');
    }

    public function revokeAllSessions(): void
    {
        if (!$this->userId) {
            return;
        }

        $currentSessionId = session()->getId();
        
        DB::table('sessions')
            ->where('user_id', $this->userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        $this->loadSessions();
        session()->flash('success', 'All other sessions have been logged out.');
    }

    public function render()
    {
        return view('livewire.settings.users.form', [
            'availableRoles' => $this->getAvailableRoles(),
            'activities' => $this->getActivities(),
        ]);
    }
}
