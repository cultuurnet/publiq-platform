<?php

declare(strict_types=1);

namespace App\Console\Commands;

trait ReadCsvFile
{
    private function readCsvFile(string $csvFile): array
    {
        $rows = [];
        /** @var resource $fileHandle */
        $fileHandle = fopen($csvFile, 'rb');

        while (!feof($fileHandle)) {
            $row = fgetcsv($fileHandle);
            if ($row === false) {
                break;
            }

            $rows[] = $row;
        }
        fclose($fileHandle);

        return $rows;
    }
}
