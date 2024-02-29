<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\AddsIdentityMethods;
use Domain\Identity;

/* final readonly */ class PaymentId implements Identity
{
    use AddsIdentityMethods;
}
