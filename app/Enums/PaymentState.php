<?php

namespace App\Enums;

use App\Enums\Contracts\HasDisplayMetadata;
use App\Enums\Contracts\ProvidesOptions;

enum PaymentState: string implements HasDisplayMetadata
{
    use ProvidesOptions;

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

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::PROCESSING => 'arrow-path',
            self::COMPLETED => 'check-circle',
            self::FAILED => 'x-circle',
            self::CANCELLED => 'minus-circle',
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
