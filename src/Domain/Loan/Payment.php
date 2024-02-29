<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\Amount;

class Payment implements \Stringable
{
    private readonly string $id;
    private readonly string $amount;
    private readonly string $debtorFirstName;
    private readonly string $debtorLastName;
    private readonly string|null $debtorSnn;
    private readonly string $reference;
    private int $state;
    private readonly \DateTimeImmutable $conductedAt;

    public function __construct(
        PaymentId $id,
        Amount $amount,
        Debtor $debtor,
        PaymentReference $reference,
        PaymentState $state = PaymentState::UNASSIGNED,
        \DateTimeImmutable $conductedAt = new \DateTimeImmutable(),
    ) {
        $this->id = $id->asString();
        $this->amount = $amount->asString();
        $this->debtorFirstName = $debtor->firstName();
        $this->debtorLastName = $debtor->lastName();
        $this->debtorSnn = $debtor->ssn();
        $this->reference = $reference->asString();
        $this->state = $state->value;
        $this->conductedAt = $conductedAt;
    }

    public function __toString(): string
    {
        return $this->reference;
    }

    public function markAsAssigned(): void
    {
        if (!$this->isUnassigned()) {
            $this->throwNotUnassigned();
        }

        $this->state = PaymentState::ASSIGNED->value;
    }

    public function markAsPartiallyAssigned(): void
    {
        if (!$this->isUnassigned()) {
            $this->throwNotUnassigned();
        }

        $this->state = PaymentState::PARTIALLY_ASSIGNED->value;
    }

    public function id(): PaymentId
    {
        return PaymentId::create($this->id);
    }

    public function state(): PaymentState
    {
        return PaymentState::from($this->state);
    }

    public function isReceived(): bool
    {
        return $this->isAssigned() || $this->isPartiallyAssigned();
    }

    public function isAssigned(): bool
    {
        return $this->isState(PaymentState::ASSIGNED);
    }

    public function isPartiallyAssigned(): bool
    {
        return $this->isState(PaymentState::PARTIALLY_ASSIGNED);
    }

    public function reference(): PaymentReference
    {
        return PaymentReference::create($this->reference);
    }

    public function debtor(): Debtor
    {
        return Debtor::create(
            $this->debtorFirstName,
            $this->debtorLastName,
            $this->debtorSnn
        );
    }

    public function amount(): Amount
    {
        return Amount::create($this->amount);
    }

    public function conductedAt(): \DateTimeImmutable
    {
        return $this->conductedAt;
    }

    private function isUnassigned(): bool
    {
        return $this->isState(PaymentState::UNASSIGNED);
    }

    private function isState(PaymentState $state): bool
    {
        return PaymentState::from($this->state) === $state;
    }

    private function throwNotUnassigned(): void
    {
        throw PaymentStateForbidden::notUnassigned(PaymentState::from($this->state));
    }
}
