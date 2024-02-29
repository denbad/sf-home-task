<?php

declare(strict_types=1);

namespace Domain\Loan\Event;

final readonly class LoanPaidOff implements DomainEvent
{
    public function __construct(
        public string $customerId,
        public string $loanNumber,
    ) {
    }
}
