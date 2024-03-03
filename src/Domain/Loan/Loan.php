<?php

declare(strict_types=1);

namespace Domain\Loan;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domain\Amount;
use Symfony\Component\Uid\Uuid;

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

    public function fulfill(PaymentRequest $paymentRequest): PaymentReference
    {
        if (!$this->isActive()) {
            $this->throwNotActive();
        }

        $credit = Amount::create($this->amountToPay)
            ->sub($paymentRequest->amount);

        $paymentId = $this->nextPaymentIdentity();

        if ($credit->isZero()) {
            $this->markAsPaid();
            $payment = Payment::asAssigned($paymentId, $paymentRequest);
        } elseif ($credit->isNegative()) {
            $this->markAsPaid();
            $this->addRefund($paymentRequest, $credit->abs());
            $payment = Payment::asPartiallyAssigned($paymentId, $paymentRequest);
        } else {
            $this->amountToPay = $credit->asString();
            $payment = Payment::asAssigned($paymentId, $paymentRequest);
        }

        $this->payments->add($payment);

        return $payment->reference();
    }

    public function id(): LoanId
    {
        return LoanId::create($this->id);
    }

    /**
     * @return iterable<int, Payment>
     */
    public function payments(): iterable
    {
        return $this->payments;
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

    private function addRefund(PaymentRequest $paymentRequest, Amount $amount): void
    {
        $this->refunds->add(new Refund(
            $this->nextRefundIdentity(),
            $paymentRequest->reference,
            $paymentRequest->debtor,
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

    private function nextPaymentIdentity(): PaymentId
    {
        return PaymentId::create($this->uuid());
    }

    private function nextRefundIdentity(): RefundId
    {
        return RefundId::create($this->uuid());
    }

    private function throwNotActive(): never
    {
        throw LoanStateForbidden::notActive(LoanState::from($this->state));
    }

    private function uuid(): string
    {
        return (string) Uuid::v7();
    }
}
