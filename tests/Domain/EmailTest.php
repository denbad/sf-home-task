<?php

declare(strict_types=1);

namespace Tests\Domain;

use Domain\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

final class EmailTest extends TestCase
{
    #[DataProvider(methodName: 'emailsForCreate')]
    public function testCreate(string $rawEmail, string|null $expectedEmail): void
    {
        if ($expectedEmail === null) {
            $this->expectException(\InvalidArgumentException::class);
            Email::create($rawEmail);
        } else {
            $actualEmail = Email::create($rawEmail)->asString();
            assertThat($actualEmail, equalTo($expectedEmail));
        }
    }

    /**
     * @return array<int, array{string, string|null}>
     */
    public static function emailsForCreate(): array
    {
        return [
            ['james.bond@gmail.com', 'james.bond@gmail.com'],
            ['JAMES.BOND@GMAIL.COM', 'james.bond@gmail.com'],
            ['j@g.c', 'j@g.c'],
            ['james.bond', null],
            ['gmail.com', null],
            ['https://google.com', null],
            ['.;:%№";@gmail.com', null],
            ['', null],
        ];
    }
}
