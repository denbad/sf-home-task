<?php

declare(strict_types=1);

namespace Util;

final readonly class DecimalHelper
{
    public const string ZERO = '0.00';
    private const int SCALE = 2;

    public static function isNegative(string $decimal, int $scale = self::SCALE): bool
    {
        return self::isLess($decimal, self::ZERO, $scale);
    }

    public static function isLess(string $decimal, string $otherDecimal, int $scale = self::SCALE): bool
    {
        return bccomp($decimal, $otherDecimal, $scale) === -1;
    }

    public static function add(string $decimalA, string $decimalB, int $scale = self::SCALE): string
    {
        return bcadd($decimalA, $decimalB, $scale);
    }

    public static function sub(string $decimalA, string $decimalB, int $scale = self::SCALE): string
    {
        return bcsub($decimalA, $decimalB, $scale);
    }

    public static function abs(string $decimal): string
    {
        if (str_starts_with($decimal, '-')) {
            return substr($decimal, offset: 1);
        }

        return $decimal;
    }
}
