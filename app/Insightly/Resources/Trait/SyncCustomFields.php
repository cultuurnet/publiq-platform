<?php

declare(strict_types=1);

namespace App\Insightly\Resources\Trait;

trait SyncCustomFields
{
    private function syncCustomFields(array $originalCustomFields, array $updatedCustomFields): array
    {
        $output = [];

        foreach ($originalCustomFields as $value) {
            $output[$value['FIELD_NAME']] = $value;
        }

        foreach ($updatedCustomFields as $value) {
            $output[$value['FIELD_NAME']] = $value;
        }

        return array_values($output);
    }
}
