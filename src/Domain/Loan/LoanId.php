<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\AddsIdentityMethods;
use Domain\Identity;

final readonly class LoanId implements Identity
{
    use AddsIdentityMethods;
}
