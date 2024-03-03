<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\Amount;

readonly class Refund
{
    private string $id;
    private string $paymentReference;
    private string $debtorFirstName;
    private string $debtorLastName;
    private string|null $debtorSnn;
    private string $amount;
    private \DateTimeImmutable $conductedAt;

    public function __construct(
        RefundId $id,
        PaymentReference $paymentReference,
        Debtor $debtor,
        Amount $amount,
        \DateTimeImmutable $conductedAt = new \DateTimeImmutable(),
    ) {
        $this->id = $id->asString();
        $this->paymentReference = $paymentReference->asString();
        $this->debtorFirstName = $debtor->firstName();
        $this->debtorLastName = $debtor->lastName();
        $this->debtorSnn = $debtor->ssn();
        $this->amount = $amount->asString();
        $this->conductedAt = $conductedAt;
    }

    public function amount(): Amount
    {
        return Amount::create($this->amount);
    }
}
