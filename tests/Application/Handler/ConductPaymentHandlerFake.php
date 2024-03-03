<?php

declare(strict_types=1);

namespace Tests\Application\Handler;

use Application\Handler\ConductPayment;
use Application\Handler\ConductPaymentHandler;
use Application\Handler\LoanMissing;
use Application\Handler\LoanStateForbidden;
use Application\Handler\PaymentStateForbidden;

final class ConductPaymentHandlerFake extends ConductPaymentHandler
{
    public function __construct(
    ) {
    }

    public function __invoke(ConductPayment $command): void
    {
        match ($command->loanNumber) {
            'LN00000001' => throw LoanMissing::missing($command->loanNumber),
            'LN00000002' => throw LoanStateForbidden::alreadyPaidOff($command->loanNumber),
            'LN00000003' => throw PaymentStateForbidden::alreadyConducted($command->reference),
            //'LN00000003' => throw LoanStateForbidden::paymentAlreadyConducted($command->reference),
            'LN00000004' => throw new \RuntimeException('Fake exception.'),
            default => null,
        };
    }
}
