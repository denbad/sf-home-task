<?php

declare(strict_types=1);

namespace Domain\Loan;

interface Loans
{
    public function nextPaymentIdentity(): PaymentId;

    public function loanById(LoanId $id): Loan|null;

    public function loanByNumber(LoanNumber $number): Loan|null;

    public function paymentById(PaymentId $id): Payment|null;

    public function paymentByReference(PaymentReference $reference): Payment|null;
}
