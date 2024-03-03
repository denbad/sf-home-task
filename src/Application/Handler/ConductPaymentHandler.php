<?php

declare(strict_types=1);

namespace Application\Handler;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Domain\Amount;
use Domain\Loan\Debtor;
use Domain\Loan\Event\LoanPaidOff;
use Domain\Loan\Event\PaymentReceived;
use Domain\Loan\Loan;
use Domain\Loan\LoanNumber;
use Domain\Loan\Loans;
use Domain\Loan\Payment;
use Domain\Loan\PaymentReference;
use Domain\Loan\PaymentState;
use Symfony\Component\Messenger\MessageBusInterface;

class ConductPaymentHandler
{
    public function __construct(
        private Loans $loans,
        private MessageBusInterface $eventBus,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(ConductPayment $command): void
    {
        $loan = $this->findLoan($command->loanNumber);

        if ($loan === null) {
            throw LoanMissing::missing($command->loanNumber);
        }

        if ($loan->isPaid()) {
            throw LoanStateForbidden::alreadyPaidOff($command->loanNumber);
        }

        if ($this->paymentExists($command->reference)) {
            // throw LoanStateForbidden::paymentAlreadyConducted($command->loanNumber);
            throw PaymentStateForbidden::alreadyConducted($command->reference);
        }

        $payment = $this->createPayment($command);
        $this->process($loan, $payment);

        if ($loan->isPaid()) {
            $this->dispatch(new LoanPaidOff(
                customerId: $loan->customerId()->asString(),
                loanNumber: $loan->loanNumber()->asString(),
            ));
        }

        if ($payment->isReceived()) {
            $this->dispatch(new PaymentReceived(
                customerId: $loan->customerId()->asString(),
                paymentReference: $payment->reference()->asString(),
            ));
        }
    }

    private function process(Loan $loan, Payment $payment): void
    {
        $manager = $this->getManager();
        $conn = $manager->getConnection();

        try {
            $conn->beginTransaction();

            $manager->lock($loan, LockMode::PESSIMISTIC_WRITE);
            $loan->fulfill($payment);
            $manager->flush();

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();

            throw $e;
        }
    }

    private function createPayment(ConductPayment $command): Payment
    {
        return new Payment(
            id: $this->loans->nextPaymentIdentity(),
            amount: Amount::create($command->amount),
            debtor: Debtor::create(
                firstName: $command->firstName,
                lastName: $command->lastName,
                ssn: $command->ssn
            ),
            reference: PaymentReference::create($command->reference),
            state: PaymentState::UNASSIGNED,
            conductedAt: new \DateTimeImmutable($command->conductedOn)
        );
    }

    private function dispatch(object $event): void
    {
        $this->eventBus->dispatch($event);
    }

    private function findLoan(string $number): Loan|null
    {
        return $this->loans->loanByNumber(LoanNumber::create($number));
    }

    private function paymentExists(string $reference): bool
    {
        $payment = $this->loans->paymentByReference(PaymentReference::create($reference));

        return $payment instanceof Payment;
    }

    private function getManager(): EntityManagerInterface
    {
        $manager = $this->managerRegistry->getManager();

        if (!$manager instanceof EntityManagerInterface) {
            throw new \RuntimeException();
        }

        return $manager;
    }
}
