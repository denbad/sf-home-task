<?php

declare(strict_types=1);

namespace Domain\Loan;

interface Customers
{
    public function byId(CustomerId $id): Customer|null;
}
