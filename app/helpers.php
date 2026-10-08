<?php

use Illuminate\Support\Str;

if (!function_exists('pluralize')) {
    /**
     * Helper to format counts with correct pluralization (e.g. "1 order", "2 orders", "1 drink").
     */
    function pluralize(int|float $count, string $singular, ?string $plural = null): string
    {
        return number_format($count) . ' ' . Str::plural($singular, $count);
    }
}
