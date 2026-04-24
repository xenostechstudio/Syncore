<?php

namespace App\Enums\Contracts;

/**
 * Shared display contract for backed enums.
 *
 * Implementations return a human-readable label, a Tailwind color
 * name (e.g. 'emerald', 'red', 'zinc') and a Heroicon name. Any
 * blade component (status-badge etc.) can then render an enum
 * uniformly without branching on string values.
 */
interface HasDisplayMetadata
{
    public function label(): string;

    public function color(): string;

    public function icon(): string;

    /**
     * @return array<string, string> map of enum value → label, for <select> options.
     */
    public static function options(): array;
}
