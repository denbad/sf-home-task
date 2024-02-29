<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domain\Amount;
use Domain\Loan\CustomerId;
use Domain\Loan\Loan;
use Domain\Loan\LoanId;
use Domain\Loan\LoanNumber;
use Domain\Loan\LoanState;

final class LoanBuilder
{
    private LoanState|null $state = null;
    private Amount|null $amountToPay = null;
    private Collection|null $payments = null;
    private Collection|null $refunds = null;

    public function build(): Loan
    {
        return new Loan(
            id: LoanId::create('foo-uuid'),
            customerId: CustomerId::create('foo-customer-uuid'),
            number: LoanNumber::create('LN87654321'),
            amountIssued: Amount::create('99.99'),
            amountToPay: $this->amountToPay ?? Amount::create('101.01'),
            state: $this->state ?? LoanState::ACTIVE,
            payments: $this->payments ?? new ArrayCollection(),
            refunds: $this->refunds ?? new ArrayCollection(),
        );
    }

    public function withState(LoanState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function withAmountToPay(Amount $amount): self
    {
        $this->amountToPay = $amount;

        return $this;
    }

    public function withPayments(Collection $payments): self
    {
        $this->payments = $payments;

        return $this;
    }

    public function withRefunds(Collection $refunds): self
    {
        $this->refunds = $refunds;

        return $this;
    }
}
