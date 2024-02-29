<?php

declare(strict_types=1);

namespace Domain\Loan;

enum LoanState: int
{
    case ACTIVE = 1;
    case PAID = 2;
}
