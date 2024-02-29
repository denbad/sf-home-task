<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Doctrine\Common\Collections\ArrayCollection;
use Domain\Amount;
use Domain\Loan\Loan;
use Domain\Loan\LoanState;
use Domain\Loan\LoanStateForbidden;
use Domain\Loan\Payment;
use Domain\Loan\PaymentState;
use Domain\Loan\Refund;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use function Tests\loan;
use function Tests\payment;

final class LoanTest extends TestCase
{
    /** @var ArrayCollection<int, Payment> */
    private ArrayCollection $payments;

    /** @var ArrayCollection<int, Refund> */
    private ArrayCollection $refunds;

    protected function setUp(): void
    {
        $this->payments = new ArrayCollection();
        $this->refunds = new ArrayCollection();
    }

    public function testItThrowsExceptionGivenStateIsNotActive(): void
    {
        $loan = $this->givenLoanIsNotActive();
        $this->expectException(LoanStateForbidden::class);
        $payment = $this->givenPayment();
        $loan->fulfill($payment);
    }

    public function testItPayOffsWithoutOverpay(): void
    {
        $loan = $this->givenLoanIsActive(amountToPay: Amount::create('100.00'));
        $payment = $this->givenPaymentWithAmount(amount: Amount::create('100.00'));

        $loan->fulfill($payment);
        $payment = $this->payments[0];
        $refund = $this->refunds[0];

        assertTrue($loan->isPaid());
        assertTrue($payment->isAssigned());
        assertTrue($payment->amount()->equals(Amount::create('100.00')));
        assertNull($refund);
    }

    public function testItPayOffsWithOverpay(): void
    {
        $loan = $this->givenLoanIsActive(amountToPay: Amount::create('99.00'));
        $payment = $this->givenPaymentWithAmount(amount: Amount::create('100.00'));

        $loan->fulfill($payment);
        $payment = $this->payments[0];
        $refund = $this->refunds[0];

        assertTrue($loan->isPaid());
        assertTrue($payment->isPartiallyAssigned());
        assertTrue($payment->amount()->equals(Amount::create('100.00')));
        assertTrue($refund->amount()->equals(Amount::create('1.00')));
    }

    public function testItPayOffsPartially(): void
    {
        $loan = $this->givenLoanIsActive(amountToPay: Amount::create('100.00'));
        $payment = $this->givenPaymentWithAmount(amount: Amount::create('99.00'));

        $loan->fulfill($payment);
        $payment = $this->payments[0];
        $refund = $this->refunds[0];

        assertFalse($loan->isPaid());
        assertTrue($payment->isAssigned());
        assertTrue($payment->amount()->equals(Amount::create('99.00')));
        assertNull($refund);
    }

    private function givenLoanIsNotActive(): Loan
    {
        return loan()
            ->withState(LoanState::PAID)
            ->build();
    }

    private function givenLoanIsActive(Amount $amountToPay): Loan
    {
        return loan()
            ->withState(LoanState::ACTIVE)
            ->withAmountToPay($amountToPay)
            ->withPayments($this->payments)
            ->withRefunds($this->refunds)
            ->build();
    }

    private function givenPaymentWithAmount(Amount $amount): Payment
    {
        return payment()
            ->withState(PaymentState::UNASSIGNED)
            ->withAmount($amount)
            ->build();
    }

    private function givenPayment(): Payment
    {
        return payment()
            ->withState(PaymentState::UNASSIGNED)
            ->build();
    }
}
