<?php

namespace App\Models\Sales;

use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTeam extends Model
{
    use HasFactory, LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'description',
        'leader_id',
        'target_amount',
        'is_active',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sales_team_members', 'sales_team_id', 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
