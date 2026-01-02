<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $fillable = [
        'type',
        'subject',
        'description',
        'activitable_type',
        'activitable_id',
        'scheduled_at',
        'completed_at',
        'duration_minutes',
        'status',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function activitable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'call' => 'phone',
            'meeting' => 'calendar',
            'email' => 'envelope',
            'task' => 'clipboard-document-check',
            'note' => 'document-text',
            default => 'chat-bubble-left',
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->type) {
            'call' => 'blue',
            'meeting' => 'violet',
            'email' => 'amber',
            'task' => 'emerald',
            'note' => 'zinc',
            default => 'zinc',
        };
    }

    public static function getTypes(): array
    {
        return [
            'call' => 'Call',
            'meeting' => 'Meeting',
            'email' => 'Email',
            'task' => 'Task',
            'note' => 'Note',
        ];
    }
}
