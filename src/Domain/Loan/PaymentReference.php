<?php

declare(strict_types=1);

namespace Domain\Loan;

use Webmozart\Assert\Assert;

final readonly class PaymentReference
{
    private function __construct(
        private string $reference,
    ) {
    }

    public static function create(string $reference): self
    {
        self::assertPaymentReference($reference);

        return new self($reference);
    }

    public function asString(): string
    {
        return strtoupper($this->reference);
    }

    private static function assertPaymentReference(string $reference): void
    {
        Assert::lengthBetween($reference, min: 8, max: 64);
        Assert::regex($reference, '/^\S+$/');
    }
}
