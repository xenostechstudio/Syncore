<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ON_LEAVE = 'on_leave';
    case PROBATION = 'probation';
    case TERMINATED = 'terminated';
    case RESIGNED = 'resigned';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::ON_LEAVE => 'On Leave',
            self::PROBATION => 'Probation',
            self::TERMINATED => 'Terminated',
            self::RESIGNED => 'Resigned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'emerald',
            self::INACTIVE => 'zinc',
            self::ON_LEAVE => 'blue',
            self::PROBATION => 'amber',
            self::TERMINATED => 'red',
            self::RESIGNED => 'orange',
        };
    }

    public function canWork(): bool
    {
        return in_array($this, [self::ACTIVE, self::PROBATION]);
    }

    public function canRequestLeave(): bool
    {
        return in_array($this, [self::ACTIVE, self::PROBATION]);
    }

    public function canBeTerminated(): bool
    {
        return in_array($this, [self::ACTIVE, self::INACTIVE, self::PROBATION, self::ON_LEAVE]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::TERMINATED, self::RESIGNED]);
    }

    public static function activeStatuses(): array
    {
        return [self::ACTIVE, self::PROBATION, self::ON_LEAVE];
    }
}
