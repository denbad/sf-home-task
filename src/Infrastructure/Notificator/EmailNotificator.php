<?php

declare(strict_types=1);

namespace Infrastructure\Notificator;

use Psr\Log\LoggerInterface;

final readonly class EmailNotificator
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function notify(EmailMessage $message): void
    {
        $this->logger->info($message);
    }
}
