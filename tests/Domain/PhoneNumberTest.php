<?php

declare(strict_types=1);

namespace Tests\Domain;

use Domain\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

final class PhoneNumberTest extends TestCase
{
    #[DataProvider(methodName: 'numbersForCreate')]
    public function testCreate(string $rawNumber, string|null $expectedNumber): void
    {
        if ($expectedNumber === null) {
            $this->expectException(\InvalidArgumentException::class);
            PhoneNumber::create($rawNumber);
        } else {
            $actualNumber = PhoneNumber::create($rawNumber)->asString();
            assertThat($actualNumber, equalTo($expectedNumber));
        }
    }

    /**
     * @return array<int, array{string, string|null}>
     */
    public static function numbersForCreate(): array
    {
        return [
            ['+123123456', '+123123456'],
            ['+1231234567', '+1231234567'],
            ['+123123456789', '+123123456789'],
            ['123123456789', null],
            ['+1 23123456', null],
            ['+1 2312345 6', null],
            ['+1 23123456 ', null],
            [' +123123456', null],
            ['+12312345', null],
            ['+12312345'.PHP_EOL, null],
            ['+1231B345', null],
            ['', null],
        ];
    }
}
