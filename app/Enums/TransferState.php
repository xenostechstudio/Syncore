<?php

namespace App\Enums;

enum TransferState: string
{
    case DRAFT = 'draft';
    case READY = 'ready';
    case IN_TRANSIT = 'in_transit';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::READY => 'Ready',
            self::IN_TRANSIT => 'In Transit',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::READY => 'blue',
            self::IN_TRANSIT => 'amber',
            self::COMPLETED => 'emerald',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil-square',
            self::READY => 'clipboard-document-check',
            self::IN_TRANSIT => 'truck',
            self::COMPLETED => 'check-circle',
            self::CANCELLED => 'x-circle',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::DRAFT => self::READY,
            self::READY => self::IN_TRANSIT,
            self::IN_TRANSIT => self::COMPLETED,
            default => null,
        };
    }

    public function nextActionLabel(): ?string
    {
        $next = $this->next();
        if (!$next) {
            return null;
        }

        return match ($next) {
            self::READY => 'Mark Ready',
            self::IN_TRANSIT => 'Mark In Transit',
            self::COMPLETED => 'Mark Completed',
            default => null,
        };
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::READY]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::DRAFT->value, 'label' => self::DRAFT->label()],
            ['key' => self::READY->value, 'label' => self::READY->label()],
            ['key' => self::IN_TRANSIT->value, 'label' => self::IN_TRANSIT->label()],
            ['key' => self::COMPLETED->value, 'label' => self::COMPLETED->label()],
        ];
    }
}
