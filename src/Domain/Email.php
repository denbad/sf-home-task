<?php

declare(strict_types=1);

namespace Domain;

use Webmozart\Assert\Assert;

final readonly class Email
{
    private function __construct(
        private string $address,
    ) {
    }

    public static function create(string $address): self
    {
        self::assertEmail($address);

        return new self($address);
    }

    public function asString(): string
    {
        return strtolower($this->address);
    }

    private static function assertEmail(string $address): void
    {
        Assert::lengthBetween($address, min: 5, max: 64);
        Assert::email($address);
    }
}
