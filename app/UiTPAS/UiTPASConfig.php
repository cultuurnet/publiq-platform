<?php

declare(strict_types=1);

namespace App\UiTPAS;

enum UiTPASConfig: string
{
    case AUTOMATIC_PERMISSIONS_ENABLED = 'uitpas.automatic_permissions_enabled';
    case TEST_ORGANISATION = 'uitpas.test.organisation';
    case TEST_API_ENDPOINT = 'uitpas.test.api_endpoint';
    case PROD_API_ENDPOINT = 'uitpas.prod.api_endpoint';
}
