<?php

declare(strict_types=1);

namespace Domain;

interface Identity
{
    public static function create(string $id): static;

    public function asString(): string;
}
