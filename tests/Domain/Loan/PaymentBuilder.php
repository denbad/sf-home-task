<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Domain\Amount;
use Domain\Loan\Debtor;
use Domain\Loan\Payment;
use Domain\Loan\PaymentId;
use Domain\Loan\PaymentReference;
use Domain\Loan\PaymentState;

final class PaymentBuilder
{
    private PaymentState|null $state = null;
    private Amount|null $amount = null;

    public function build(): Payment
    {
        return new Payment(
            id: PaymentId::create('foo-uuid'),
            amount: $this->amount ?? Amount::create('3999.99'),
            debtor: Debtor::create(
                firstName: 'James',
                lastName: 'Bond',
                ssn: 'SNN12345678'
            ),
            reference: PaymentReference::create('RF12345678'),
            state: $this->state ?? PaymentState::UNASSIGNED,
            conductedAt: new \DateTimeImmutable()
        );
    }

    public function withState(PaymentState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function withAmount(Amount $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
