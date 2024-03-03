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
use Domain\Loan\PaymentReference;
use Domain\Loan\PaymentRequest;
use Domain\Loan\PaymentResult;
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

        if ($this->loanConducted($command->reference)) {
            throw LoanStateForbidden::paymentAlreadyConducted($command->reference);
        }

        $paymentRequest = $this->createPaymentRequest($command);
        $paymentResult = $this->process($loan, $paymentRequest);

        if ($loan->isPaid()) {
            $this->dispatch(new LoanPaidOff(
                customerId: $loan->customerId()->asString(),
                loanNumber: $loan->loanNumber()->asString(),
            ));
        }

        $this->dispatch(new PaymentReceived(
            customerId: $loan->customerId()->asString(),
            paymentReference: $paymentResult->reference->asString(),
        ));
    }

    private function process(Loan $loan, PaymentRequest $paymentRequest): PaymentResult
    {
        $manager = $this->getManager();
        $conn = $manager->getConnection();

        try {
            $conn->beginTransaction();

            $manager->lock($loan, LockMode::PESSIMISTIC_WRITE);
            $paymentResult = $loan->fulfill($paymentRequest);
            $manager->flush();

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();

            throw $e;
        }

        return $paymentResult;
    }

    private function createPaymentRequest(ConductPayment $command): PaymentRequest
    {
        return new PaymentRequest(
            amount: Amount::create($command->amount),
            debtor: Debtor::create(
                firstName: $command->firstName,
                lastName: $command->lastName,
                ssn: $command->ssn
            ),
            reference: PaymentReference::create($command->reference),
            conductedAt: new \DateTimeImmutable($command->conductedOn)
        );
    }

    private function dispatch(object $event): void
    {
        $this->eventBus->dispatch($event);
    }

    private function findLoan(string $number): Loan|null
    {
        return $this->loans->byNumber(LoanNumber::create($number));
    }

    private function loanConducted(string $reference): bool
    {
        return $this->loans->conductedExists(PaymentReference::create($reference));
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
