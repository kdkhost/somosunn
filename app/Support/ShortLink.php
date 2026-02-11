<?php

namespace App\Support;

final class ShortLink
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const BASE = 62;

    private function __construct()
    {
        //
    }

    public static function encodeId(int $id): string
    {
        if ($id < 0) {
            throw new \InvalidArgumentException('ID inválido para encode.');
        }

        if ($id === 0) {
            return '0';
        }

        $alphabet = self::ALPHABET;
        $base = self::BASE;
        $encoded = '';

        while ($id > 0) {
            $encoded = $alphabet[$id % $base] . $encoded;
            $id = intdiv($id, $base);
        }

        return $encoded;
    }

    public static function decodeId(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $alphabet = self::ALPHABET;
        $base = self::BASE;

        static $index = null;
        if (!is_array($index)) {
            $index = [];
            $len = strlen($alphabet);
            for ($i = 0; $i < $len; $i++) {
                $index[$alphabet[$i]] = $i;
            }
        }

        $decoded = 0;
        $chars = str_split($value);

        foreach ($chars as $char) {
            if (!array_key_exists($char, $index)) {
                return null;
            }

            $digit = (int) $index[$char];
            if ($decoded > intdiv(PHP_INT_MAX - $digit, $base)) {
                return null;
            }

            $decoded = ($decoded * $base) + $digit;
        }

        return $decoded;
    }

    public static function encodeProduct(string $type, int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        $prefix = match ($type) {
            'course' => 'c',
            'mentorship' => 'm',
            'event' => 'e',
            default => null,
        };

        if ($prefix === null) {
            return null;
        }

        return $prefix . self::encodeId($id);
    }

    /**
     * @return array{type:string,id:int}|null
     */
    public static function decodeProduct(string $code): ?array
    {
        $code = trim($code);
        if (strlen($code) < 2) {
            return null;
        }

        $prefix = $code[0];
        $id = self::decodeId(substr($code, 1));

        if (!$id || $id <= 0) {
            return null;
        }

        $type = match ($prefix) {
            'c' => 'course',
            'm' => 'mentorship',
            'e' => 'event',
            default => null,
        };

        if ($type === null) {
            return null;
        }

        return [
            'type' => $type,
            'id' => $id,
        ];
    }
}

