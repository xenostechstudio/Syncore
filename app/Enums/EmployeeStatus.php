<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case ACTIVE = 'active';
    case ON_LEAVE = 'on_leave';
    case SUSPENDED = 'suspended';
    case RESIGNED = 'resigned';
    case TERMINATED = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ON_LEAVE => 'On Leave',
            self::SUSPENDED => 'Suspended',
            self::RESIGNED => 'Resigned',
            self::TERMINATED => 'Terminated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::ON_LEAVE => 'yellow',
            self::SUSPENDED => 'orange',
            self::RESIGNED => 'gray',
            self::TERMINATED => 'red',
        };
    }

    public static function transitions(): array
    {
        return [
            self::ACTIVE->value => [self::ON_LEAVE->value, self::SUSPENDED->value, self::RESIGNED->value, self::TERMINATED->value],
            self::ON_LEAVE->value => [self::ACTIVE->value, self::RESIGNED->value, self::TERMINATED->value],
            self::SUSPENDED->value => [self::ACTIVE->value, self::TERMINATED->value],
            self::RESIGNED->value => [],
            self::TERMINATED->value => [],
        ];
    }
}
