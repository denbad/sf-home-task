<?php

declare(strict_types=1);

namespace Domain\Loan;

interface Loans
{
    public function loanById(LoanId $id): Loan|null;

    public function loanByNumber(LoanNumber $number): Loan|null;

    public function paymentByReference(PaymentReference $reference): Payment|null;

    /**
     * @param \DateTimeImmutable $conductedOn
     *
     * @return iterable<int, Payment>
     */
    public function paymentsByDate(\DateTimeImmutable $conductedOn): iterable;
}
