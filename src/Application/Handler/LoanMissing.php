<?php

declare(strict_types=1);

namespace Application\Handler;

final class LoanMissing extends \RuntimeException
{
    public static function missing(string $number): self
    {
        return new self(sprintf('Loan "%s" not found.', $number));
    }
}
