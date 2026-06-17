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

        if (strlen($digits) === 11 && str_starts_with($digits, '55') && self::looksLikeCountryCodePrefix($digits)) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 9) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 3),
                substr($digits, 5, 4)
            );
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

    private static function looksLikeCountryCodePrefix(string $digits): bool
    {
        $possibleDdd = substr($digits, 2, 2);
        if (!in_array($possibleDdd, self::validDdds(), true)) {
            return false;
        }

        return !str_starts_with($possibleDdd, '9');
    }

    private static function validDdds(): array
    {
        return [
            '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '21', '22', '24', '27', '28',
            '31', '32', '33', '34', '35', '37', '38',
            '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '51', '53', '54', '55',
            '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '71', '73', '74', '75', '77', '79',
            '81', '82', '83', '84', '85', '86', '87', '88', '89',
            '91', '92', '93', '94', '95', '96', '97', '98', '99',
        ];
    }
}
