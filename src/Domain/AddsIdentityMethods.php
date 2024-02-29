<?php

declare(strict_types=1);

namespace Domain;

trait AddsIdentityMethods
{
    private readonly string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function create(string $id): static
    {
        return new static($id);
    }

    public function asString(): string
    {
        return $this->id;
    }
}
