<?php

declare(strict_types=1);

namespace Domain\Loan\Event;

final readonly class PaymentReceived implements DomainEvent
{
    public function __construct(
        public string $customerId,
        public string $paymentReference,
    ) {
    }
}
