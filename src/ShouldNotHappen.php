<?php

declare(strict_types=1);

namespace App;

final class ShouldNotHappen extends \LogicException
{
    public function __construct(
        string $message = 'Should not happen.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct(message: $message, previous: $previous);
    }
}
