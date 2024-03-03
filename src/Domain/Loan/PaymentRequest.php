<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\Amount;

final readonly class PaymentRequest
{
    public function __construct(
        public Amount $amount,
        public Debtor $debtor,
        public PaymentReference $reference,
        public \DateTimeImmutable $conductedAt = new \DateTimeImmutable(),
    ) {
    }
}
