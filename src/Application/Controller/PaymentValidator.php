<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Validator;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PaymentValidator extends Validator
{
    protected function constraints(): Collection
    {
        return new Collection([
            'firstname' => [
                new NotBlank(),
                new Length(['min' => 2, 'max' => 64]),
                new Regex('/^[a-zA-Z]+$/D'),
            ],
            'lastname' => [
                new NotBlank(),
                new Length(['min' => 2, 'max' => 64]),
                new Regex('/^[a-zA-Z]+$/D'),
            ],
            'paymentDate' => [
                new NotBlank(),
                new DateTime(['format' => \DateTimeInterface::ATOM]),
                new Callback([
                    'callback' => static function ($paymentDate, ExecutionContextInterface $context): void {
                        try {
                            if (new \DateTimeImmutable($paymentDate) > new \DateTimeImmutable()) {
                                $context
                                    ->buildViolation('This value is not a valid payment date.')
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
            'refId' => [
                new NotBlank(),
                new Length(['min' => 9, 'max' => 64]),
                new Regex('/^\S+$/'),
            ],
        ]);
    }
}
