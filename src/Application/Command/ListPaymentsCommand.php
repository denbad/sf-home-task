<?php

declare(strict_types=1);

namespace Application\Command;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Loan\Payment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ListPaymentsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct(name: 'app:payments:list');
    }

    protected function configure(): void
    {
        $this->addOption('date', mode: InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('date'))) {
            return 0;
        }

        $date = new \DateTimeImmutable($input->getOption('date'));
        $this->renderTable($output, $this->loadPayments($date));

        return 0;
    }

    /**
     * @param OutputInterface        $output
     * @param iterable<int, Payment> $payments
     */
    private function renderTable(OutputInterface $output, iterable $payments): void
    {
        $table = (new Table($output))
            ->setHeaders(['#', 'Id', 'Amount', 'Firstname', 'Lastname', 'Snn', 'Reference', 'State', 'Date']);

        foreach ($payments as $i => $payment) {
            $table->addRow([
                $i + 1,
                $payment->id()->asString(),
                $payment->amount()->asString(),
                $payment->debtor()->firstName(),
                $payment->debtor()->lastName(),
                $payment->debtor()->ssn(),
                $payment->reference()->asString(),
                $payment->state()->name,
                $payment->conductedAt()->format('Y-m-d H:i:s'),
            ]);
        }

        $table->render();
    }

    /**
     * @param \DateTimeImmutable $conductedOn
     *
     * @return iterable<int, Payment>
     */
    private function loadPayments(\DateTimeImmutable $conductedOn): iterable
    {
        return $this->em
            ->createQueryBuilder()
            ->addSelect('p')
            ->from(Payment::class, 'p')
            ->andWhere('p.conductedAt BETWEEN :dateFrom AND :dateTo')
            ->setParameter('dateFrom', $conductedOn->setTime(0, 0))
            ->setParameter('dateTo', $conductedOn->setTime(23, 59, 59))
            ->orderBy('p.conductedAt', 'ASC')
            ->getQuery()
            ->toIterable();
    }
}
