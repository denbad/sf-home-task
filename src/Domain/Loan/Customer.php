<?php

declare(strict_types=1);

namespace Domain\Loan;

use Domain\Email;
use Domain\PhoneNumber;

final readonly class Customer implements \Stringable
{
    private string $id;
    private string $firstName;
    private string $lastName;
    private string|null $ssn;
    private string|null $phoneNumber;
    private string|null $email;

    public function __construct(
        CustomerId $id,
        Debtor $debtor,
        PhoneNumber $phoneNumber,
        Email $email,
    ) {
        $this->id = $id->asString();
        $this->firstName = $debtor->firstName();
        $this->lastName = $debtor->lastName();
        $this->ssn = $debtor->ssn();
        $this->phoneNumber = $phoneNumber->asString();
        $this->email = $email->asString();
    }

    public function __toString(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function phoneNumber(): PhoneNumber|null
    {
        if ($this->phoneNumber === null) {
            return null;
        }

        return PhoneNumber::create($this->phoneNumber);
    }

    public function email(): Email|null
    {
        if ($this->email === null) {
            return null;
        }

        return Email::create($this->email);
    }
}
