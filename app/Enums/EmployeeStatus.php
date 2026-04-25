<?php

namespace App\Enums;

use App\Enums\Contracts\HasDisplayMetadata;
use App\Enums\Contracts\ProvidesOptions;

enum EmployeeStatus: string implements HasDisplayMetadata
{
    use ProvidesOptions;

    case ACTIVE = 'active';
    case ON_LEAVE = 'on_leave';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ON_LEAVE => 'On Leave',
            self::INACTIVE => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'emerald',
            self::ON_LEAVE => 'amber',
            self::INACTIVE => 'zinc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'check-circle',
            self::ON_LEAVE => 'calendar-days',
            self::INACTIVE => 'pause-circle',
        };
    }

    public static function transitions(): array
    {
        return [
            self::ACTIVE->value => [self::ON_LEAVE->value, self::INACTIVE->value],
            self::ON_LEAVE->value => [self::ACTIVE->value, self::INACTIVE->value],
            self::INACTIVE->value => [],
        ];
    }
}
