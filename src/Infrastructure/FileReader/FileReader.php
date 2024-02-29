<?php

declare(strict_types=1);

namespace Infrastructure\FileReader;

interface FileReader
{
    public function supports(string $format): bool;

    /**
     * @param string $filePath
     *
     * @return iterable<int, mixed>
     */
    public function read(string $filePath): iterable;
}
