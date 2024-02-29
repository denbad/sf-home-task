<?php

declare(strict_types=1);

namespace Domain\Loan\Event;

use Domain\Loan\Customer;
use Domain\Loan\CustomerId;
use Domain\Loan\Customers;
use Infrastructure\Notificator\EmailMessage;
use Infrastructure\Notificator\EmailNotificator;
use Infrastructure\Notificator\SmsMessage;
use Infrastructure\Notificator\SmsNotificator;
use Psr\Log\LoggerInterface;

final readonly class NotifyCustomer
{
    public function __construct(
        private Customers $customers,
        private EmailNotificator $emailNotificator,
        private SmsNotificator $smsNotificator,
        private LoggerInterface $logger,
    ) {
    }

    public function whenLoanPaidOff(LoanPaidOff $event): void
    {
        $customer = $this->findCustomer($event->customerId);

        if ($customer === null) {
            return;
        }

        $tpl = 'Dear %s, the loan %s has been completely paid off! Congratulations!';
        $message = sprintf($tpl, $customer, $event->loanNumber);
        $this->notifyCustomer($customer, $message);
    }

    public function whenPaymentReceived(PaymentReceived $event): void
    {
        $customer = $this->findCustomer($event->customerId);

        if ($customer === null) {
            return;
        }

        $tpl = 'Dear %s, the payment %s has been successfully conducted! Thank you!';
        $message = sprintf($tpl, $customer, $event->paymentReference);
        $this->notifyCustomer($customer, $message);
    }

    private function findCustomer(string $customerId): Customer|null
    {
        return $this->customers->byId(CustomerId::create($customerId));
    }

    private function notifyCustomer(Customer $customer, string $message): void
    {
        try {
            [$email, $phoneNumber] = [
                $customer->email()?->asString(),
                $customer->phoneNumber()?->asString(),
            ];

            if ($email !== null) {
                $this->emailNotificator->notify(new EmailMessage(address: $email, body: $message));
            }

            if ($phoneNumber !== null) {
                $this->smsNotificator->notify(new SmsMessage(address: $phoneNumber, body: $message));
            }
        } catch (\Throwable $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
