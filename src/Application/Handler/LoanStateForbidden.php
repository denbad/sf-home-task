<?php

declare(strict_types=1);

namespace Application\Handler;

final class LoanStateForbidden extends \RuntimeException
{
    public static function alreadyPaidOff(string $number): self
    {
        return new self(sprintf('Loan "%s" already paid off.', $number));
    }

    public static function paymentAlreadyConducted(string $reference): self
    {
        return new self(sprintf('Payment "%s" already conducted.', $reference));
    }
}
