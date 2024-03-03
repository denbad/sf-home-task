<?php

declare(strict_types=1);

namespace Tests;

use Tests\Domain\Loan\LoanBuilder;
use Tests\Domain\Loan\PaymentRequestBuilder;

function loan(): LoanBuilder
{
    return new LoanBuilder();
}

function paymentRequest(): PaymentRequestBuilder
{
    return new PaymentRequestBuilder();
}
