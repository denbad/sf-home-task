<?php

declare(strict_types=1);

namespace Domain;

use Webmozart\Assert\Assert;

final readonly class PhoneNumber
{
    private function __construct(
        private string $number,
    ) {
    }

    public static function create(string $number): self
    {
        self::assertPhoneNumber($number);

        return new self($number);
    }

    public function asString(): string
    {
        return $this->number;
    }

    private static function assertPhoneNumber(string $number): void
    {
        Assert::lengthBetween($number, min: 10, max: 18);
        Assert::regex($number, '/^\+\d+$/D');
    }
}
