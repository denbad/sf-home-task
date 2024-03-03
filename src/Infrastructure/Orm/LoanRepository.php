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

    public function loanById(LoanId $id): Loan|null
    {
        return $this->find($id->asString());
    }

    public function loanByNumber(LoanNumber $number): Loan|null
    {
        return $this->findOneBy([
            'reference' => $number->asString(),
        ]);
    }

    // to do start: return Loan
    public function paymentByReference(PaymentReference $reference): Payment|null
    {
        return $this->getEntityManager()
            ->getRepository(Payment::class)
            ->findOneBy(['reference' => $reference->asString()]);
    }

    public function paymentsByDate(\DateTimeImmutable $conductedOn): iterable
    {
        return $this->getEntityManager()
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
    // to do end: return Loan
}
