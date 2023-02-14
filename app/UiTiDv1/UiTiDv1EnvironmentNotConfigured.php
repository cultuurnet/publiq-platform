<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use Exception;

final class UiTiDv1EnvironmentNotConfigured extends Exception
{
    public function __construct(UiTiDv1Environment $uiTiDv1Environment)
    {
        parent::__construct('No configuration found for UiTiD v1 environment ' . $uiTiDv1Environment->value);
    }
}
