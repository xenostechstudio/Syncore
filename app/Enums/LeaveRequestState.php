<?php

namespace App\Enums;

enum LeaveRequestState: string
{
    case DRAFT    = 'draft';
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT     => 'Draft',
            self::PENDING   => 'Pending',
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT     => 'zinc',
            self::PENDING   => 'amber',
            self::APPROVED  => 'emerald',
            self::REJECTED  => 'red',
            self::CANCELLED => 'zinc',
        };
    }

    public function icon(): ?string
    {
        return match($this) {
            self::DRAFT     => 'pencil-square',
            self::PENDING   => 'clock',
            self::APPROVED  => 'check-circle',
            self::REJECTED  => 'x-circle',
            self::CANCELLED => 'x-mark',
        };
    }

    public function canSubmit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canApprove(): bool
    {
        return $this === self::PENDING;
    }

    public function canReject(): bool
    {
        return $this === self::PENDING;
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::DRAFT->value,    'label' => self::DRAFT->label()],
            ['key' => self::PENDING->value,  'label' => self::PENDING->label()],
            ['key' => self::APPROVED->value, 'label' => self::APPROVED->label()],
        ];
    }
}
