<?php

namespace App\Support;

class Phone
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return null;
        }

        // Keep country code if present; Nicaragua mobiles are typically 8 digits local.
        return $digits;
    }
}
