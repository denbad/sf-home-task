<?php

declare(strict_types=1);

namespace Domain\Loan;

use Webmozart\Assert\Assert;

final readonly class Debtor
{
    private function __construct(
        private string $firstName,
        private string $lastName,
        private string|null $ssn,
    ) {
    }

    public static function create(string $firstName, string $lastName, string|null $ssn): self
    {
        self::assertName($firstName);
        self::assertName($lastName);
        self::assertSsn($ssn);

        return new self($firstName, $lastName, $ssn);
    }

    public function firstName(): string
    {
        return $this->name($this->firstName);
    }

    public function lastName(): string
    {
        return $this->name($this->lastName);
    }

    public function ssn(): string|null
    {
        if (empty($this->ssn)) {
            return null;
        }

        Assert::lengthBetween($this->ssn, min: 10, max: 64);

        return strtoupper($this->ssn);
    }

    private static function assertName(string $name): void
    {
        Assert::lengthBetween($name, min: 2, max: 64);
        Assert::regex($name, '/^[a-zA-Z]+$/D');
    }

    private static function assertSsn(string|null $ssn): void
    {
        if (empty($ssn)) {
            return;
        }

        Assert::lengthBetween($ssn, min: 8, max: 64);
        Assert::regex($ssn, '/^[a-zA-Z0-9]+$/D');
    }

    private function name(string $name): string
    {
        return ucfirst(strtolower($name));
    }
}
