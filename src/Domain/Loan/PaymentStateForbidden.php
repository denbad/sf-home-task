<?php

declare(strict_types=1);

namespace Domain\Loan;

final class PaymentStateForbidden extends \LogicException
{
    public static function notUnassigned(PaymentState $actual): self
    {
        return self::forbiddenState(PaymentState::UNASSIGNED, $actual);
    }

    private static function forbiddenState(PaymentState $expected, PaymentState $actual): self
    {
        return new self(sprintf('Expected payment state is "%s", but "%s" provided.', $expected->name, $actual->name));
    }
}
