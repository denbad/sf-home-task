<?php

declare(strict_types=1);

namespace Tests;

use Tests\Domain\Loan\LoanBuilder;
use Tests\Domain\Loan\PaymentBuilder;

function loan(): LoanBuilder
{
    return new LoanBuilder();
}

function payment(): PaymentBuilder
{
    return new PaymentBuilder();
}
