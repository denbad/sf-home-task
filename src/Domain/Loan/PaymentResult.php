<?php

declare(strict_types=1);

namespace Domain\Loan;

final readonly class PaymentResult
{
    public function __construct(
        public PaymentReference $reference,
    ) {
    }
}
