<?php

declare(strict_types=1);

namespace Application\Command;

use Application\Validator;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PaymentValidator extends Validator
{
    protected function constraints(): Collection
    {
        return new Collection([
            'payerName' => [
                new NotBlank(),
                new Length(['min' => 2, 'max' => 64]),
                new Regex('/^[a-zA-Z]+$/D'),
            ],
            'payerSurname' => [
                new NotBlank(),
                new Length(['min' => 2, 'max' => 64]),
                new Regex('/^[a-zA-Z]+$/D'),
            ],
            'paymentDate' => [
                new NotBlank(),
                new AtLeastOneOf([
                    new DateTime(['format' => 'YmdHis']),
                    new DateTime(['format' => \DateTimeInterface::RFC2822]),
                    new DateTime(['format' => \DateTimeInterface::ATOM]),
                ]),
                new Callback([
                    'callback' => static function ($paymentDate, ExecutionContextInterface $context): void {
                        try {
                            if (new \DateTimeImmutable($paymentDate) > new \DateTimeImmutable()) {
                                $context
                                    ->buildViolation('This value is not a valid payment date. !!!')
                                    ->addViolation();
                            }
                        } catch (\Throwable) {
                        }
                    },
                ]),
            ],
            'amount' => [
                new NotBlank(),
                new Positive(),
                new Regex('/^\d+\.\d{2}$/'),
            ],
            'description' => [
                new NotBlank(),
                new Regex('/^LN\d{8}$/i'),
            ],
            'paymentReference' => [
                new NotBlank(),
                new Length(['min' => 9, 'max' => 64]),
                new Regex('/^\S+$/'),
            ],
            'nationalSecurityNumber' => [
                new AtLeastOneOf([
                    new Optional(),
                    new All([
                        new Length(['min' => 8, 'max' => 64]),
                        new Regex('/^[a-zA-Z0-9]+$/D'),
                    ]),
                ]),
            ],
        ]);
    }
}
