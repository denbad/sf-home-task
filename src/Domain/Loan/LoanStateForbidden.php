<?php

declare(strict_types=1);

namespace Domain\Loan;

final class LoanStateForbidden extends \LogicException
{
    public static function notActive(LoanState $actual): self
    {
        return self::forbiddenState(LoanState::ACTIVE, $actual);
    }

    private static function forbiddenState(LoanState $expected, LoanState $actual): self
    {
        return new self(sprintf('Expected loan state is "%s", but "%s" provided.', $expected->name, $actual->name));
    }
}
