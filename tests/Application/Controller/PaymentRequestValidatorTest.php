<?php

declare(strict_types=1);

namespace Tests\Application\Controller;

use Application\Controller\PaymentValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

final class PaymentRequestValidatorTest extends TestCase
{
    private PaymentValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PaymentValidator();
    }

    /**
     * @param array<string, string> $data
     * @param array<string, string> $expectedErrorKeys
     */
    #[DataProvider(methodName: 'dataForValidate')]
    public function testValidate(array $data, array $expectedErrorKeys): void
    {
        $actualErrors = array_keys($this->validator->validate($data));
        assertThat($actualErrors, equalTo($expectedErrorKeys));
    }

    /**
     * @return array<string, mixed>
     */
    public static function dataForValidate(): array
    {
        return [
            'empty data' => [
                [],
                [
                    'firstname',
                    'lastname',
                    'paymentDate',
                    'amount',
                    'description',
                    'refId',
                ],
            ],
            'extra data' => [
                [
                    'username' => 'james',
                    'password' => 'qwerty',
                ],
                [
                    'firstname',
                    'lastname',
                    'paymentDate',
                    'amount',
                    'description',
                    'refId',
                    'username',
                    'password',
                ],
            ],
            'all invalid data' => [
                [
                    'firstname' => 'J',
                    'lastname' => 'BondBondBondBondBondBondBondBondBondBondBondBondBondBondBondBondBondBondBondBondBon',
                    'paymentDate' => '2022-99-12T15:19:21+00:00',
                    'amount' => '-99.99',
                    'description' => 'GG9',
                    'refId' => '1-fff',
                ],
                [
                    'firstname',
                    'lastname',
                    'paymentDate',
                    'amount',
                    'description',
                    'refId',
                ],
            ],
            'all valid data' => [
                [
                    'firstname' => 'James',
                    'lastname' => 'Bond',
                    'paymentDate' => '2022-12-12T15:19:21+00:00',
                    'amount' => '99.99',
                    'description' => 'LN20221212',
                    'refId' => '130f8a89-51c9-47d0-a6ef-1aea54924d3b',
                ],
                [],
            ],
        ];
    }
}
