<?php

declare(strict_types=1);

namespace Application\Handler;

final class PaymentStateForbidden extends \RuntimeException
{
    public static function alreadyConducted(string $reference): self
    {
        return new self(sprintf('Payment "%s" already conducted.', $reference));
    }
}
