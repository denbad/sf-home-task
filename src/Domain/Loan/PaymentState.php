<?php

declare(strict_types=1);

namespace Domain\Loan;

enum PaymentState: int
{
    case ASSIGNED = 1;
    case PARTIALLY_ASSIGNED = 2;
}
