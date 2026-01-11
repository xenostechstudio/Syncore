<?php

namespace App\Enums;

enum DeliveryOrderState: string
{
    case PENDING = 'pending';
    case PICKED = 'picked';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PICKED => 'Picked',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::RETURNED => 'Returned',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'zinc',
            self::PICKED => 'blue',
            self::IN_TRANSIT => 'amber',
            self::DELIVERED => 'emerald',
            self::FAILED => 'red',
            self::RETURNED => 'violet',
            self::CANCELLED => 'zinc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::PICKED => 'cube',
            self::IN_TRANSIT => 'truck',
            self::DELIVERED => 'check-circle',
            self::FAILED => 'exclamation-circle',
            self::RETURNED => 'arrow-uturn-left',
            self::CANCELLED => 'x-circle',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::PENDING => self::PICKED,
            self::PICKED => self::IN_TRANSIT,
            self::IN_TRANSIT => self::DELIVERED,
            default => null,
        };
    }

    public function nextActionLabel(): ?string
    {
        $next = $this->next();
        if (! $next) {
            return null;
        }

        return match ($next) {
            self::PICKED => 'Mark Picked',
            self::IN_TRANSIT => 'Mark In Transit',
            self::DELIVERED => 'Mark Delivered',
            default => null,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::DELIVERED, self::FAILED, self::RETURNED, self::CANCELLED], true);
    }

    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }

    public static function steps(): array
    {
        return [
            ['key' => self::PENDING->value, 'label' => self::PENDING->label()],
            ['key' => self::PICKED->value, 'label' => self::PICKED->label()],
            ['key' => self::IN_TRANSIT->value, 'label' => self::IN_TRANSIT->label()],
            ['key' => self::DELIVERED->value, 'label' => self::DELIVERED->label()],
        ];
    }
}
