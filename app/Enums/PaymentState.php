<?php

namespace App\Enums;

enum PaymentState: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'zinc',
            self::PROCESSING => 'blue',
            self::COMPLETED => 'emerald',
            self::FAILED => 'red',
            self::REFUNDED => 'amber',
            self::CANCELLED => 'zinc',
        };
    }

    public function canRefund(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::REFUNDED, self::CANCELLED]);
    }

    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }
}
