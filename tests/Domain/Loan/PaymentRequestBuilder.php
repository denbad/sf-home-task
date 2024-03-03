<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Domain\Amount;
use Domain\Loan\Debtor;
use Domain\Loan\PaymentReference;
use Domain\Loan\PaymentRequest;

final class PaymentRequestBuilder
{
    private Amount|null $amount = null;

    public function build(): PaymentRequest
    {
        return new PaymentRequest(
            amount: $this->amount ?? Amount::create('3999.99'),
            debtor: Debtor::create(
                firstName: 'James',
                lastName: 'Bond',
                ssn: 'SNN12345678'
            ),
            reference: PaymentReference::create('RF12345678'),
            conductedAt: new \DateTimeImmutable()
        );
    }

    public function withAmount(Amount $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
