<?php

declare(strict_types=1);

namespace Tests\Domain;

use Domain\Amount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\equalTo;

final class AmountTest extends TestCase
{
    #[DataProvider(methodName: 'decimalsForFormString')]
    public function testCreate(string $rawAmount, string|null $expectedAmount): void
    {
        if ($expectedAmount === null) {
            $this->expectException(\InvalidArgumentException::class);
            Amount::create($rawAmount);
        } else {
            $actualAmount = Amount::create($rawAmount)->asString();
            assertThat($actualAmount, equalTo($expectedAmount));
        }
    }

    #[DataProvider(methodName: 'amountsForAdd')]
    public function testAdd(Amount $amountA, Amount $amountB, Amount $expectedAmount): void
    {
        $actualAmount = $amountA->add($amountB);
        assertTrue($actualAmount->equals($expectedAmount));
    }

    #[DataProvider(methodName: 'amountsForSub')]
    public function testSub(Amount $amountA, Amount $amountB, Amount $expectedAmount): void
    {
        $actualAmount = $amountA->sub($amountB);
        assertTrue($actualAmount->equals($expectedAmount));
    }

    #[DataProvider(methodName: 'amountsForIsNegative')]
    public function testIsNegative(Amount $amount, bool $expectedFlag): void
    {
        $actualFlag = $amount->isNegative();
        assertThat($actualFlag, equalTo($expectedFlag));
    }

    #[DataProvider(methodName: 'amountsForIsZero')]
    public function testIsZero(Amount $amount, bool $expectedFlag): void
    {
        $actualFlag = $amount->isZero();
        assertThat($actualFlag, equalTo($expectedFlag));
    }

    #[DataProvider(methodName: 'amountsForAbs')]
    public function testAbs(Amount $amount, Amount $expectedAmount): void
    {
        $actualAmount = $amount->abs();
        assertThat($actualAmount, equalTo($expectedAmount));
    }

    /**
     * @return array<int, array{string, string|null}>
     */
    public static function decimalsForFormString(): array
    {
        return [
            ['0.00', '0.00'],
            ['99.01', '99.01'],
            ['-0.01', null],
            ['-99.01', null],
            ['0,00', null],
            ['0.00a', null],
            ['non-numeric', null],
        ];
    }

    /**
     * @return array<string, array{Amount, Amount, Amount}>
     */
    public static function amountsForAdd(): array
    {
        return [
            '0.01 + 9.99' => [
                Amount::create('0.01'),
                Amount::create('9.99'),
                Amount::create('10.00'),
            ],
            '0.01 + 0.01' => [
                Amount::create('0.01'),
                Amount::create('0.01'),
                Amount::create('0.02'),
            ],
            '10.01 + 15.09' => [
                Amount::create('10.01'),
                Amount::create('15.09'),
                Amount::create('25.10'),
            ],
            '0.01 + 1.10' => [
                Amount::create('0.01'),
                Amount::create('1.10'),
                Amount::create('1.11'),
            ],
            '0.01 + -9.99' => [
                Amount::create('0.01'),
                Amount::createNegative('-9.99'),
                Amount::createNegative('-9.98'),
            ],
        ];
    }

    /**
     * @return array<string, array{Amount, Amount, Amount}>
     */
    public static function amountsForSub(): array
    {
        return [
            '0.01 - 9.99' => [
                Amount::create('0.01'),
                Amount::create('9.99'),
                Amount::createNegative('-9.98'),
            ],
            '0.01 - 0.01' => [
                Amount::create('0.01'),
                Amount::create('0.01'),
                Amount::create('0.00'),
            ],
            '10.01 - 15.09' => [
                Amount::create('10.01'),
                Amount::create('15.09'),
                Amount::createNegative('-5.08'),
            ],
            '0.01 - 1.10' => [
                Amount::create('0.01'),
                Amount::create('1.10'),
                Amount::createNegative('-1.09'),
            ],
            '10.01 - -15.09' => [
                Amount::create('10.01'),
                Amount::createNegative('-15.09'),
                Amount::create('25.10'),
            ],
        ];
    }

    /**
     * @return array<string, array{Amount, bool}>
     */
    public static function amountsForIsNegative(): array
    {
        return [
            '-0.01' => [Amount::createNegative('-0.01'), true],
            '-1.01' => [Amount::createNegative('-1.01'), true],
            '0.00' => [Amount::create('0.00'), false],
            '0.01' => [Amount::create('0.01'), false],
            '1.01' => [Amount::create('1.01'), false],
        ];
    }

    /**
     * @return array<string, array{Amount, bool}>
     */
    public static function amountsForIsZero(): array
    {
        return [
            '0.00' => [Amount::create('0.00'), true],
            '0.01' => [Amount::create('0.01'), false],
            '1.00' => [Amount::create('1.00'), false],
            '-0.01' => [Amount::createNegative('-0.01'), false],
            '-1.00' => [Amount::createNegative('-1.00'), false],
        ];
    }

    /**
     * @return array<string, array{Amount, Amount}>
     */
    public static function amountsForAbs(): array
    {
        return [
            '0.00' => [Amount::create('0.00'), Amount::create('0.00')],
            '0.01' => [Amount::create('0.01'), Amount::create('0.01')],
            '-0.01' => [Amount::createNegative('-0.01'), Amount::create('0.01')],
        ];
    }
}
