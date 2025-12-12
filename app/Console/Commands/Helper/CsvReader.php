<?php

declare(strict_types=1);

namespace App\Console\Commands\Helper;

final class CsvReader
{
    public function readCsvFile(string $path): array
    {
        $csvData = [];

        if (($handle = fopen($path, 'rb')) !== false) {
            $headers = fgetcsv($handle, 0, ';');

            if ($headers === false) {
                return [];
            }

            // Filter out null values from headers
            $headers = array_filter($headers, fn ($header) => $header !== null);

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) === count($headers)) {
                    // Filter out null values from row data
                    $row = array_filter($row, fn ($value) => $value !== null);

                    if (count($row) === count($headers)) {
                        $csvData[] = array_combine($headers, $row);
                    }
                }
            }

            fclose($handle);
        }

        return $csvData;
    }
}
