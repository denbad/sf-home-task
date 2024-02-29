<?php

declare(strict_types=1);

namespace Infrastructure\Notificator;

use Psr\Log\LoggerInterface;

final readonly class SmsNotificator
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function notify(SmsMessage $message): void
    {
        $this->logger->info($message);
    }
}
