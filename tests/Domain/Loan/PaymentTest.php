<?php

declare(strict_types=1);

namespace Tests\Domain\Loan;

use Domain\Loan\Payment;
use Domain\Loan\PaymentState;
use Domain\Loan\PaymentStateForbidden;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertTrue;
use function Tests\payment;

final class PaymentTest extends TestCase
{
    public function testMarkAsAssigned(): void
    {
        $payment = $this->createPayment();
        $payment->markAsAssigned();
        assertTrue($payment->isAssigned());

        $this->expectException(PaymentStateForbidden::class);
        $payment->markAsAssigned();
    }

    public function testMarkAsPartiallyAssigned(): void
    {
        $payment = $this->createPayment();
        $payment->markAsPartiallyAssigned();
        assertTrue($payment->isPartiallyAssigned());

        $this->expectException(PaymentStateForbidden::class);
        $payment->markAsPartiallyAssigned();
    }

    public function testIsReceived(): void
    {
        $payment = $this->createPayment();
        $payment->markAsAssigned();
        assertTrue($payment->isReceived());

        $payment = $this->createPayment();
        $payment->markAsPartiallyAssigned();
        assertTrue($payment->isReceived());
    }

    private function createPayment(): Payment
    {
        return payment()
            ->withState(PaymentState::UNASSIGNED)
            ->build();
    }
}
