<?php

declare(strict_types=1);

namespace Infrastructure\Orm;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Domain\Loan\Loan;
use Domain\Loan\LoanId;
use Domain\Loan\LoanNumber;
use Domain\Loan\Loans;
use Domain\Loan\Payment;
use Domain\Loan\PaymentReference;

final class LoanRepository extends ServiceEntityRepository implements Loans
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function byId(LoanId $id): Loan|null
    {
        return $this->find($id->asString());
    }

    public function byNumber(LoanNumber $number): Loan|null
    {
        return $this->findOneBy([
            'reference' => $number->asString(),
        ]);
    }

    public function conductedExists(PaymentReference $reference): bool
    {
        return $this->findPaymentByReference($reference) instanceof Payment;
    }

    public function byConductedOn(\DateTimeImmutable $conductedOn): iterable
    {
        return $this->createQueryBuilder('l')
            ->addSelect('p')
            ->andWhere('p.conductedAt BETWEEN :dateFrom AND :dateTo')
            ->setParameter('dateFrom', $conductedOn->setTime(0, 0))
            ->setParameter('dateTo', $conductedOn->setTime(23, 59, 59))
            ->getQuery()
            ->toIterable();
    }

    private function findPaymentByReference(PaymentReference $reference): Payment|null
    {
        return $this->getEntityManager()
            ->getRepository(Payment::class)
            ->findOneBy(['reference' => $reference->asString()]);
    }
}
