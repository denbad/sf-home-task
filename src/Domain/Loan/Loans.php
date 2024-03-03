<?php

declare(strict_types=1);

namespace Domain\Loan;

interface Loans
{
    public function byId(LoanId $id): Loan|null;

    public function byNumber(LoanNumber $number): Loan|null;

    public function conductedExists(PaymentReference $reference): bool;

    /**
     * @param \DateTimeImmutable $conductedOn
     *
     * @return iterable<int, Loan>
     */
    public function byConductedOn(\DateTimeImmutable $conductedOn): iterable;
}
