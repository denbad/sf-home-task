<?php

declare(strict_types=1);

namespace Infrastructure\Orm;

use Symfony\Component\Uid\Uuid;

trait AddsRepositoryHelperMethods
{
    private function uuid(): string
    {
        return (string) Uuid::v7();
    }
}
