<?php

declare(strict_types=1);

namespace Domain\Loan;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domain\Amount;

class Loan
{
    private readonly string $id;
    private readonly string $customerId;
    private readonly string $reference;
    private readonly string $amountIssued;
    private string $amountToPay;
    private int $state;
    private Collection $payments;
    private Collection $refunds;

    public function __construct(
        LoanId $id,
        CustomerId $customerId,
        LoanNumber $number,
        Amount $amountIssued,
        Amount $amountToPay,
        LoanState $state = LoanState::ACTIVE,
        Collection $payments = new ArrayCollection(),
        Collection $refunds = new ArrayCollection(),
    ) {
        $this->id = $id->asString();
        $this->customerId = $customerId->asString();
        $this->reference = $number->asString();
        $this->amountIssued = $amountIssued->asString();
        $this->amountToPay = $amountToPay->asString();
        $this->state = $state->value;
        $this->payments = $payments;
        $this->refunds = $refunds;
    }

    public function fulfill(Payment $payment): void
    {
        if (!$this->isActive()) {
            $this->throwNotActive();
        }

        $credit = Amount::create($this->amountToPay)
            ->sub($payment->amount());

        if ($credit->isZero()) {
            $this->markAsPaid();
            $payment->markAsAssigned();
        } elseif ($credit->isNegative()) {
            $this->markAsPaid();
            $this->addRefund($payment, $credit->abs());
            $payment->markAsPartiallyAssigned();
        } else {
            $this->amountToPay = $credit->asString();
            $payment->markAsAssigned();
        }

        $this->payments->add($payment);
    }

    public function id(): LoanId
    {
        return LoanId::create($this->id);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::create($this->customerId);
    }

    public function loanNumber(): LoanNumber
    {
        return LoanNumber::create($this->reference);
    }

    public function isPaid(): bool
    {
        return $this->isState(LoanState::PAID);
    }

    private function markAsPaid(): void
    {
        if (!$this->isActive()) {
            $this->throwNotActive();
        }

        $this->amountToPay = Amount::zero()->asString();
        $this->state = LoanState::PAID->value;
    }

    private function addRefund(Payment $payment, Amount $amount): void
    {
        $this->refunds->add(new Refund(
            $payment->id(),
            $payment->reference(),
            $payment->debtor(),
            $amount
        ));
    }

    private function isActive(): bool
    {
        return $this->isState(LoanState::ACTIVE);
    }

    private function isState(LoanState $state): bool
    {
        return LoanState::from($this->state) === $state;
    }

    private function throwNotActive(): never
    {
        throw LoanStateForbidden::notActive(LoanState::from($this->state));
    }
}
