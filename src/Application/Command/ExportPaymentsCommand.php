<?php

declare(strict_types=1);

namespace Application\Command;

use Application\Event\PaymentFailed;
use Application\Handler\ConductPayment;
use Application\Handler\ConductPaymentHandler;
use Application\Handler\LoanStateForbidden;
use Application\Handler\PaymentStateForbidden;
use Infrastructure\FileReader\FileReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ExportPaymentsCommand extends Command
{
    private const int STATUS_DUPLICATE_ENTRY = 1;
    private const int STATUS_NEGATIVE_AMOUNT = 2;
    private const int STATUS_INVALID_DATE = 3;
    private const int STATUS_VALIDATION_ERROR = 4;
    private const int STATUS_PROCESS_ERROR = 5;
    private const int STATUS_ALL_FINE = 0;

    public function __construct(
        private readonly FileReader $fileReader,
        private readonly PaymentValidator $paymentValidator,
        private readonly ConductPaymentHandler $paymentHandler,
        private readonly MessageBusInterface $eventBus,
    ) {
        parent::__construct(name: 'app:payments:export');
    }

    protected function configure(): void
    {
        $this->addOption('file', mode: InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('file'))) {
            return self::STATUS_ALL_FINE;
        }

        $filePath = __DIR__.'/'.$input->getOption('file');

        $status = $this->validateAllUntilFirstError($filePath);

        if ($status !== self::STATUS_ALL_FINE) {
            $output->writeln($status.': Validation errors');

            return $status;
        }

        try {
            $this->processImport($filePath);
            [$message, $error] = ['ok', null];
        } catch (LoanStateForbidden|PaymentStateForbidden $e) {
            [$status, $message, $error] = [self::STATUS_DUPLICATE_ENTRY, $e->getMessage(), $e->getMessage()];
        } catch (\Throwable $e) {
            [$status, $message, $error] = [self::STATUS_PROCESS_ERROR, $e->getMessage(), $e->getMessage()];
        }

        if ($error !== null) {
            $event = new PaymentFailed(url: 'app://command-line', data: [$filePath], errors: [$error]);
            $this->eventBus->dispatch($event);
        }

        $output->writeln($status.': '.$message);

        return $status;
    }

    private function validateAllUntilFirstError(string $filePath): int
    {
        foreach ($this->readFile($filePath) as $data) {
            $errors = $this->paymentValidator->validate($data);

            if (isset($errors['amount'])) {
                return self::STATUS_NEGATIVE_AMOUNT;
            }

            if (isset($errors['paymentDate'])) {
                return self::STATUS_INVALID_DATE;
            }

            if (count($errors) > 0) {
                return self::STATUS_VALIDATION_ERROR;
            }
        }

        return self::STATUS_ALL_FINE;
    }

    private function processImport(string $filePath): void
    {
        foreach ($this->readFile($filePath) as $data) {
            $command = new ConductPayment(
                firstName: $data['payerName'],
                lastName: $data['payerSurname'],
                conductedOn: $data['paymentDate'],
                amount: $data['amount'],
                loanNumber: $data['description'],
                reference: $data['paymentReference'],
                ssn: $data['nationalSecurityNumber'],
            );

            ($this->paymentHandler)($command);
        }
    }

    /**
     * @param string $filePath
     *
     * @return iterable<int ,mixed>
     */
    private function readFile(string $filePath): iterable
    {
        return $this->fileReader->read($filePath);
    }
}
