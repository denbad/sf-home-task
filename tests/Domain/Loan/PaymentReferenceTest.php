<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Domain\Loan\PaymentReference;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

final class PaymentReferenceTest extends TestCase
{
    #[DataProvider(methodName: 'referencesForCreate')]
    public function testCreate(string $rawReference, string|null $expectedReference): void
    {
        if ($expectedReference === null) {
            $this->expectException(\InvalidArgumentException::class);
            PaymentReference::create($rawReference);
        } else {
            $actualReference = PaymentReference::create($rawReference)->asString();
            assertThat($actualReference, equalTo($expectedReference));
        }
    }

    /**
     * @return array<int, array{string, string|null}>
     */
    public static function referencesForCreate(): array
    {
        return [
            ['1234fght5699', '1234FGHT5699'],
            ['1234fght5699 ', null],
            [' 1234fght5699 ', null],
            ['1234f'.PHP_EOL.'ght569', null],
            ['1234567', null],
            [str_repeat('a', 65), null],
            ['', null],
        ];
    }
}
