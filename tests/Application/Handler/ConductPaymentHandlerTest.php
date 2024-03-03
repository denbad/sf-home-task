<?php

declare(strict_types=1);

namespace Tests\Application\Handler;

use Application\Handler\ConductPayment;
use Application\Handler\ConductPaymentHandler;
use Application\Handler\LoanMissing;
use Application\Handler\LoanStateForbidden;
use Application\Handler\PaymentStateForbidden;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Domain\Amount;
use Domain\Loan\Loans;
use Domain\Loan\LoanState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\Application\EventBusSpy;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;
use function Tests\loan;
use function Tests\payment;

final class ConductPaymentHandlerTest extends TestCase
{
    /** @var array<int, string> */
    public array $spy;
    private Loans&MockObject $loans;
    private MessageBusInterface $eventBus;
    private Connection&MockObject $conn;
    private EntityManagerInterface&MockObject $manager;
    private ManagerRegistry&MockObject $managerRegistry;
    private ConductPaymentHandler $handler;

    protected function setUp(): void
    {
        $this->spy = [];
        $this->loans = $this->createLoans();
        $this->eventBus = $this->createEventBus();
        $this->conn = $this->createConn();
        $this->manager = $this->createManager();
        $this->managerRegistry = $this->createManagerRegistry();

        $this->handler = $this->createHandler(
            $this->loans,
            $this->eventBus,
            $this->managerRegistry
        );
    }

    public function testItThrowsExceptionGivenLoanNotFound(): void
    {
        $this->givenLoanNotFound();
        $this->expectException(LoanMissing::class);
        $this->handle(amount: Amount::create('99.9'));
    }

    public function testItThrowsExceptionGivenLoanIsPaidOff(): void
    {
        $this->givenLoanFound(state: LoanState::PAID);
        $this->expectException(LoanStateForbidden::class);
        $this->handle(amount: Amount::create('99.9'));
    }

    public function testItThrowsExceptionGivenPaymentAlreadyConducted(): void
    {
        $this->givenLoanFound(state: LoanState::ACTIVE);
        $this->givenPaymentAlreadyConducted();
        $this->expectException(PaymentStateForbidden::class);
        $this->handle(amount: Amount::create('99.9'));
    }

    public function testItRollsBackTransactionOnExceptionThrown(): void
    {
        $this->givenLoanFound(state: LoanState::ACTIVE);
        $this->givenPaymentNotConductedYet();
        $this->givenExceptionIsThrownInTheProcess();

        try {
            $this->handle(amount: Amount::create('99.9'));
        } catch (\Throwable) {
        }

        assertThat($this->spy, equalTo([
            'begintransaction',
            'entitylocked',
            'rollbacktransaction',
        ]));
    }

    public function testItDispatchEventPaymentIsReceived(): void
    {
        $this->givenLoanFound(state: LoanState::ACTIVE);
        $this->givenPaymentNotConductedYet();
        $this->handle(amount: Amount::create('99.9'));

        assertThat($this->spy, equalTo([
            'begintransaction',
            'entitylocked',
            'committransaction',
            'paymentreceived',
        ]));
    }

    public function testItDispatchEventGivenLoanIsPaidOff(): void
    {
        $this->givenLoanFound(state: LoanState::ACTIVE);
        $this->givenPaymentNotConductedYet();
        $this->handle(amount: Amount::create('100.01'));

        assertThat($this->spy, equalTo([
            'begintransaction',
            'entitylocked',
            'committransaction',
            'loanpaidoff',
            'paymentreceived',
        ]));
    }

    private function handle(Amount $amount): void
    {
        ($this->handler)($this->createCommand($amount));
    }

    private function givenLoanNotFound(): void
    {
        $this->loans
            ->method('byNumber')
            ->willReturn(null);
    }

    private function givenLoanFound(LoanState $state): void
    {
        $this->loans
            ->method('byNumber')
            ->willReturn(loan()
                ->withState($state)
                ->withAmountToPay(Amount::create('100.00'))
                ->build()
            );
    }

    private function givenPaymentAlreadyConducted(): void
    {
        $this->loans
            ->method('conductedExists')
            ->willReturn(payment()->build());
    }

    private function givenPaymentNotConductedYet(): void
    {
        $this->loans
            ->method('conductedExists')
            ->willReturn(null);
    }

    private function givenExceptionIsThrownInTheProcess(): void
    {
        $this->manager
            ->method('lock')
            ->willThrowException(new \RuntimeException());
    }

    private function createCommand(Amount $amount): ConductPayment
    {
        return new ConductPayment(
            firstName: 'James',
            lastName: 'Bond',
            conductedOn: (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            amount: $amount->asString(),
            loanNumber: 'LN12345678',
            reference: 'RFC12345678',
            ssn: 'SSN12345678',
        );
    }

    private function createLoans(): Loans&MockObject
    {
        return $this->createMock(Loans::class);
    }

    private function createEventBus(): MessageBusInterface
    {
        return new EventBusSpy($this->spy);
    }

    private function createConn(): Connection&MockObject
    {
        $conn = $this->createMock(Connection::class);
        $conn
            ->method('beginTransaction')
            ->willReturnCallback(function () {
                $this->spy[] = 'begintransaction';

                return true;
            });
        $conn
            ->method('commit')
            ->willReturnCallback(function () {
                $this->spy[] = 'committransaction';

                return true;
            });
        $conn
            ->method('rollBack')
            ->willReturnCallback(function () {
                $this->spy[] = 'rollbacktransaction';

                return true;
            });

        return $conn;
    }

    private function createManager(): EntityManagerInterface&MockObject
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager
            ->method('getConnection')
            ->willReturn($this->conn);
        $manager
            ->method('lock')
            ->willReturnCallback(function () {
                $this->spy[] = 'entitylocked';

                return true;
            });

        return $manager;
    }

    private function createManagerRegistry(): ManagerRegistry&MockObject
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('getManager')
            ->willReturn($this->manager);

        return $registry;
    }

    private function createHandler(
        Loans $loans,
        MessageBusInterface $eventBus,
        ManagerRegistry $managerRegistry,
    ): ConductPaymentHandler {
        return new ConductPaymentHandler($loans, $eventBus, $managerRegistry);
    }
}
