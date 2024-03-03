<?php

declare(strict_types=1);

namespace Application\Command;

use Domain\Loan\Loan;
use Domain\Loan\Loans;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ListPaymentsCommand extends Command
{
    public function __construct(
        private readonly Loans $loans,
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
        $this->renderTable($output, $this->loadLoans($date));

        return 0;
    }

    /**
     * @param OutputInterface     $output
     * @param iterable<int, Loan> $loans
     */
    private function renderTable(OutputInterface $output, iterable $loans): void
    {
        /** @var ConsoleOutput $output */
        $table = new Table($output->section());
        $table
            ->setHeaders(['#', 'Amount', 'Firstname', 'Lastname', 'Snn', 'Reference', 'State', 'Date'])
            ->render();

        foreach ($loans as $i => $loan) {
            foreach ($loan->payments() as $payment) {
                $table->appendRow([
                    $i + 1,
                    $payment->amount()->asString(),
                    $payment->debtor()->firstName(),
                    $payment->debtor()->lastName(),
                    $payment->debtor()->ssn(),
                    $payment->reference()->asString(),
                    $payment->state()->name,
                    $payment->conductedAt()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * @param \DateTimeImmutable $conductedOn
     *
     * @return iterable<int, Loan>
     */
    private function loadLoans(\DateTimeImmutable $conductedOn): iterable
    {
        return $this->loans->byConductedOn($conductedOn);
    }
}
