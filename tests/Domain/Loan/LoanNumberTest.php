<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Domain\Loan\LoanNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

final class LoanNumberTest extends TestCase
{
    #[DataProvider(methodName: 'numbersForCreate')]
    public function testCreate(string $rawNumber, string|null $expectedNumber): void
    {
        if ($expectedNumber === null) {
            $this->expectException(\InvalidArgumentException::class);
            LoanNumber::create($rawNumber);
        } else {
            $actualNumber = LoanNumber::create($rawNumber)->asString();
            assertThat($actualNumber, equalTo($expectedNumber));
        }
    }

    /**
     * @return array<int, array{string, string|null}>
     */
    public static function numbersForCreate(): array
    {
        return [
            ['ln12345678', 'LN12345678'],
            ['LN87654321', 'LN87654321'],
            ['ln123'.PHP_EOL.'45678', null],
            ['ln123'.PHP_EOL.PHP_EOL.'456', null],
            ['ln1234567 ', null],
            [' ln1234567', null],
            ['ln1234567', null],
            ['1234567899', null],
            ['12345678ln', null],
            ['1234ln5678', null],
            ['', null],
        ];
    }
}
