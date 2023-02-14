<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

trait ReadCsvFile
{
    private function readCsvFile(string $csvFile): array
    {
        $rows = [];
        /** @var resource $fileHandle */
        $fileHandle = fopen($csvFile, 'rb');

        while (!feof($fileHandle)) {
            $rows[] = fgetcsv($fileHandle);
        }
        fclose($fileHandle);

        return $rows;
    }
}
