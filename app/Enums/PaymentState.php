<?php

namespace App\Enums;

enum PaymentState: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'blue',
            self::COMPLETED => 'green',
            self::FAILED => 'red',
            self::CANCELLED => 'gray',
        };
    }

    public static function transitions(): array
    {
        return [
            self::PENDING->value => [self::PROCESSING->value, self::CANCELLED->value],
            self::PROCESSING->value => [self::COMPLETED->value, self::FAILED->value],
            self::COMPLETED->value => [],
            self::FAILED->value => [self::PENDING->value],
            self::CANCELLED->value => [],
        ];
    }
}
