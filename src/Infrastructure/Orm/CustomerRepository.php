<?php

declare(strict_types=1);

namespace Infrastructure\Orm;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Domain\Loan\Customer;
use Domain\Loan\CustomerId;
use Domain\Loan\Customers;

final class CustomerRepository extends ServiceEntityRepository implements Customers
{
    use AddsRepositoryHelperMethods;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function byId(CustomerId $id): Customer|null
    {
        return $this->find($id->asString());
    }
}
