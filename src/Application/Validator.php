<?php

declare(strict_types=1);

namespace Application;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validation;

abstract class Validator
{
    /**
     * @param array<string, string> $data
     *
     * @return array<string, string>
     */
    final public function validate(array $data): array
    {
        $errors = [];
        $violations = Validation::createValidator()->validate($data, $this->constraints());

        foreach ($violations as $violation) {
            $key = trim($violation->getPropertyPath(), '[]');
            $errors[$key] = $violation->getMessage();
        }

        return $errors;
    }

    abstract protected function constraints(): Collection;
}
