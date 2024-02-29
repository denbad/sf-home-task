<?php

declare(strict_types=1);

namespace Infrastructure\FileReader;

final readonly class CsvReader implements FileReader
{
    public function supports(string $format): bool
    {
        return $format === 'csv';
    }

    public function read(string $filePath): iterable
    {
        ['extension' => $format] = pathinfo($filePath);

        if (!$this->supports($format)) {
            throw new \RuntimeException(sprintf('CsvReader does not support format "%s".', $format));
        }

        $handle = fopen($filePath, 'r');
        $columns = fgetcsv($handle);

        while (($values = fgetcsv($handle)) !== false) {
            yield array_combine($columns, $values);
        }

        fclose($handle);
    }
}
