<?php

namespace App\Support;

class BrazilPhone
{
    public static function digits(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === null) {
            return '';
        }

        if (strlen($digits) > 11 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    public static function normalize(?string $value): ?string
    {
        $original = trim((string) $value);
        if ($original === '') {
            return null;
        }

        $digits = self::digits($original);
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10) {
            $ddd = substr($digits, 0, 2);
            $local = substr($digits, 2);

            if ($local !== '' && preg_match('/^[6-9]/', $local) === 1) {
                $digits = $ddd . '9' . $local;
            }
        }

        if (strlen($digits) === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7, 4)
            );
        }

        if (strlen($digits) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 4),
                substr($digits, 6, 4)
            );
        }

        return $original;
    }

    public static function format(?string $value): ?string
    {
        return self::normalize($value);
    }
}
