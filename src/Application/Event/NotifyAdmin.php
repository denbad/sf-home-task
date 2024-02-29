<?php

declare(strict_types=1);

namespace Application\Event;

use Infrastructure\Notificator\EmailMessage;
use Infrastructure\Notificator\EmailNotificator;
use Psr\Log\LoggerInterface;

final readonly class NotifyAdmin
{
    public function __construct(
        private EmailNotificator $emailNotificator,
        private LoggerInterface $logger,
    ) {
    }

    public function whenPaymentFailed(PaymentFailed $event): void
    {
        try {
            $message = 'Hello admin, a payment has failed to conduct! Take a look!';
            $this->notifyAdmin('support@example.com', $message);
        } catch (\Throwable $e) {
            $this->logger->info($e->getMessage());
        }
    }

    private function notifyAdmin(string $email, string $message): void
    {
        $this->emailNotificator->notify(new EmailMessage(address: $email, body: $message));
    }
}
