<?php

declare(strict_types=1);

namespace Domain\Loan\Event;

use Domain\Loan\Customer;
use Domain\Loan\CustomerId;
use Domain\Loan\Customers;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

final readonly class NotifyCustomer
{
    public function __construct(
        private Customers $customers,
        private NotifierInterface $notifier,
        private LoggerInterface $logger,
    ) {
    }

    public function whenLoanPaidOff(LoanPaidOff $event): void
    {
        $customer = $this->findCustomer($event->customerId);

        if ($customer === null) {
            return;
        }

        [$subject, $text] = [
            sprintf('The loan %s has been paid off!', $event->loanNumber),
            sprintf('Dear %s, the loan %s has been paid off! Congratulations!', $customer, $event->loanNumber),
        ];

        $this->notifyCustomer($customer, $subject, $text);
    }

    public function whenPaymentReceived(PaymentReceived $event): void
    {
        $customer = $this->findCustomer($event->customerId);

        if ($customer === null) {
            return;
        }

        [$subject, $text] = [
            sprintf('The payment %s has been conducted!', $event->paymentReference),
            sprintf('Dear %s, the payment %s has been conducted! Awesome!', $customer, $event->paymentReference),
        ];

        $this->notifyCustomer($customer, $subject, $text);
    }

    private function findCustomer(string $customerId): Customer|null
    {
        return $this->customers->byId(CustomerId::create($customerId));
    }

    private function notifyCustomer(Customer $customer, string $subject, string $text): void
    {
        [$email, $phoneNumber] = [
            (string) $customer->email()?->asString(),
            (string) $customer->phoneNumber()?->asString(),
        ];

        $notification = (new Notification(
            subject: $subject,
            channels: ['email', 'sms'],
        ))->content($text);

        $recipient = new Recipient($email, $phoneNumber);

        try {
            $this->notifier->send($notification, $recipient);
        } catch (\Throwable $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
