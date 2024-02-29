<?php

declare(strict_types=1);

namespace Tests\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Util\DecimalHelper;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\equalTo;

final class DecimalHelperTest extends TestCase
{
    #[DataProvider(methodName: 'decimalsForIsNegative')]
    public function testIsNegative(string $positiveDecimal): void
    {
        $actualFlag = DecimalHelper::isNegative('-'.$positiveDecimal);
        assertTrue($actualFlag);

        $actualFlag = DecimalHelper::isNegative($positiveDecimal);
        assertFalse($actualFlag);
    }

    #[DataProvider(methodName: 'decimalsForIsLess')]
    public function testIsLess(string $decimalA, string $decimalB): void
    {
        $actualFlag = DecimalHelper::isLess($decimalA, $decimalB);
        assertTrue($actualFlag);

        $actualFlag = DecimalHelper::isLess($decimalB, $decimalA);
        assertFalse($actualFlag);
    }

    #[DataProvider(methodName: 'decimalsForAdd')]
    public function testAdd(string $decimalA, string $decimalB, string $expectedDecimal): void
    {
        $actualDecimal = DecimalHelper::add($decimalA, $decimalB);
        assertThat($actualDecimal, equalTo($expectedDecimal));
    }

    #[DataProvider(methodName: 'decimalsForSub')]
    public function testSub(string $decimalA, string $decimalB, string $expectedDecimal): void
    {
        $actualDecimal = DecimalHelper::sub($decimalA, $decimalB);
        assertThat($actualDecimal, equalTo($expectedDecimal));
    }

    #[DataProvider(methodName: 'decimalsForAbs')]
    public function testAbs(string $decimal, string $expectedDecimal): void
    {
        $actualDecimal = DecimalHelper::abs($decimal);
        assertThat($actualDecimal, equalTo($expectedDecimal));
    }

    /**
     * @return array<int, array{string}>
     */
    public static function decimalsForIsNegative(): array
    {
        return [
            ['0.01'],
            ['0.1'],
            ['1'],
            ['1.01'],
            ['1.1'],
            ['99'],
        ];
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function decimalsForIsLess(): array
    {
        return [
            ['10', '10.1'],
            ['10', '10.01'],
            ['10', '10.99'],
            ['10', '11.00'],
            ['10', '11.00'],
            ['0', '0.1'],
            ['0', '0.01'],
            ['-10', '10.1'],
            ['-10', '10.01'],
            ['-10', '10.99'],
            ['-10', '11.00'],
            ['-10', '11.00'],
            ['-10.01', '-10'],
            ['-10.99', '-10'],
            ['-10.999', '-10'],
            ['-10.9999', '-10'],
            ['-11.00', '-10'],
            ['-11.00', '-10'],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function decimalsForAdd(): array
    {
        return [
            '0.01 + 9.99' => ['0.01', '9.99', '10.00'],
            '0.01 + -0.01' => ['0.01', '-0.01', '0.00'],
            '-0.01 + -9.99' => ['-0.01', '-9.99', '-10.00'],
            '-0.01 + 9.99' => ['-0.01', '9.99', '9.98'],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function decimalsForSub(): array
    {
        return [
            '0.01 - 0.01' => ['0.01', '0.01', '0.00'],
            '-0.01 - -0.01' => ['-0.01', '-0.01', '0.00'],
            '1.01 - 0.01' => ['1.01', '0.01', '1.00'],
            '0.01 - 1.01' => ['0.01', '1.01', '-1.00'],
            '-9.99 - 0.01' => ['-9.99', '0.01', '-10.00'],
            '0.00 - 0.00' => ['0.00', '0.00', '0.00'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function decimalsForAbs(): array
    {
        return [
            '-1.09' => ['-1.09', '1.09'],
            '1.09' => ['1.09', '1.09'],
            '-0.00' => ['-0.00', '0.00'],
            '0.00' => ['0.00', '0.00'],
        ];
    }
}
