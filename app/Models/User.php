<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Sales\SalesTeam;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'working_hours_start',
        'working_hours_end',
        'working_days',
        'out_of_office_start',
        'out_of_office_end',
        'out_of_office_message',
        'language',
        'timezone',
        'signature',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'working_days' => 'array',
            'out_of_office_start' => 'date',
            'out_of_office_end' => 'date',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Check if user is currently out of office
     */
    public function isOutOfOffice(): bool
    {
        if (!$this->out_of_office_start || !$this->out_of_office_end) {
            return false;
        }

        $today = now()->startOfDay();
        return $today->between($this->out_of_office_start, $this->out_of_office_end);
    }

    /**
     * Get the sales teams this user belongs to
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(SalesTeam::class, 'sales_team_members', 'user_id', 'sales_team_id');
    }
}
