<?php

declare(strict_types=1);

namespace Application\Handler;

final readonly class ConductPayment
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $conductedOn,
        public string $amount,
        public string $loanNumber,
        public string $reference,
        public string|null $ssn = null,
    ) {
    }
}
