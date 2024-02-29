<?php

declare(strict_types=1);

namespace Domain\Loan;

use Webmozart\Assert\Assert;

final readonly class LoanNumber
{
    private function __construct(
        private string $number,
    ) {
    }

    public static function create(string $number): self
    {
        self::assertLoanNumber($number);

        return new self($number);
    }

    public function asString(): string
    {
        return strtoupper($this->number);
    }

    private static function assertLoanNumber(string $number): void
    {
        Assert::regex($number, '/^LN\d{8}$/i');
    }
}
