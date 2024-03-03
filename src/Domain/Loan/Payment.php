<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\Amount;

readonly class Payment implements \Stringable
{
    private string $id;
    private string $amount;
    private string $debtorFirstName;
    private string $debtorLastName;
    private string|null $debtorSnn;
    private string $reference;
    private int $state;
    private \DateTimeImmutable $conductedAt;

    private function __construct(
        PaymentId $id,
        Amount $amount,
        Debtor $debtor,
        PaymentReference $reference,
        PaymentState $state,
        \DateTimeImmutable $conductedAt,
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

    public static function asAssigned(PaymentId $id, PaymentRequest $paymentRequest): self
    {
        return self::create($id, $paymentRequest, PaymentState::ASSIGNED);
    }

    public static function asPartiallyAssigned(PaymentId $id, PaymentRequest $paymentRequest): self
    {
        return self::create($id, $paymentRequest, PaymentState::PARTIALLY_ASSIGNED);
    }

    public function id(): PaymentId
    {
        return PaymentId::create($this->id);
    }

    public function state(): PaymentState
    {
        return PaymentState::from($this->state);
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

    private static function create(PaymentId $id, PaymentRequest $paymentRequest, PaymentState $state): self
    {
        return new self(
            id: $id,
            amount: $paymentRequest->amount,
            debtor: $paymentRequest->debtor,
            reference: $paymentRequest->reference,
            state: $state,
            conductedAt: $paymentRequest->conductedAt
        );
    }

    private function isState(PaymentState $state): bool
    {
        return PaymentState::from($this->state) === $state;
    }
}
