<?php

namespace App\Enums\Contracts;

/**
 * Default implementation of the options() method from HasDisplayMetadata.
 *
 * Assumes the using enum has a label() method.
 */
trait ProvidesOptions
{
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
