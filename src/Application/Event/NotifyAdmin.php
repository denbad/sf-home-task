<?php

declare(strict_types=1);

namespace Application\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Webmozart\Assert\Assert;

final readonly class NotifyAdmin
{
    public function __construct(
        private NotifierInterface $notifier,
        private LoggerInterface $logger,
        private string $supportEmail,
    ) {
        Assert::email($this->supportEmail);
    }

    public function whenPaymentFailed(PaymentFailed $event): void
    {
        [$subject, $text] = [
            'A payment has failed to conduct!',
            'Take a look:'.PHP_EOL.implode(PHP_EOL, $event->errors),
        ];

        $this->notifyAdmin($subject, $text);
    }

    private function notifyAdmin(string $subject, string $text): void
    {
        $notification = (new Notification(
            subject: $subject,
            channels: ['email'],
        ))->content($text);

        $recipient = new Recipient($this->supportEmail);

        try {
            $this->notifier->send($notification, $recipient);
        } catch (\Throwable $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
