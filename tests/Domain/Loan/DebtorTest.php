<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Domain\Loan\Debtor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

final class DebtorTest extends TestCase
{
    /**
     * @param array{string, string, string|null}      $rawData
     * @param array{string, string, string|null}|null $expectedData
     */
    #[DataProvider(methodName: 'dataForCreate')]
    public function testCreate(array $rawData, array|null $expectedData): void
    {
        if ($expectedData === null) {
            $this->expectException(\InvalidArgumentException::class);
            Debtor::create(...$rawData);
        } else {
            $actualDebtor = Debtor::create(...$rawData);

            $actualValues = [
                $actualDebtor->firstName(),
                $actualDebtor->lastName(),
                $actualDebtor->ssn(),
            ];

            assertThat($actualValues, equalTo($expectedData));
        }
    }

    /**
     * @return array<int, array{array{string, string, string|null}, array{string, string, string|null}|null}>
     */
    public static function dataForCreate(): array
    {
        return [
            [
                ['jameS', 'bonD', 'fgt8765411'],
                ['James', 'Bond', 'FGT8765411'],
            ],
            [
                ['jAMES', 'bOND', null],
                ['James', 'Bond', null],
            ],
            [
                ['jAMES22', 'bOND', 'fgt8765411'],
                null,
            ],
            [
                ['jAMES', 'bOND22', 'fgt8765411'],
                null,
            ],
            [
                ['j', 'Bond', 'fgt8765411'],
                null,
            ],
            [
                ['James', 'B', 'fgt8765411'],
                null,
            ],
            [
                ['James', 'Bond', '1234567'],
                null,
            ],
            [
                ['Ja mes', 'Bond', '1234567'],
                null,
            ],
            [
                ['James', 'Bon d', '1234567'],
                null,
            ],
            [
                ['James', 'Bond', '123456 7'],
                null,
            ],
            [
                [str_repeat('a', 65), 'Bond', '12345678'],
                null,
            ],
            [
                ['James', str_repeat('a', 65), '12345678'],
                null,
            ],
            [
                ['James', 'Bond', str_repeat('a', 65)],
                null,
            ],
        ];
    }
}
