<?php

declare(strict_types=1);

namespace App\UiTPAS;

enum UiTPASConfig: string
{
    case REALM_NAME = 'uitid';

    case AUTOMATIC_PERMISSIONS_ENABLED = 'uitpas.automatic_permissions_enabled';
    case TEST_ORGANISATION = 'uitpas.test.organisation';
    case TEST_API_ENDPOINT = 'uitpas.test.api_endpoint';
    case PROD_API_ENDPOINT = 'uitpas.prod.api_endpoint';

    case UITPAS_TEST_OAUTH_TOKEN_URL = 'uitpas.test.oath_token_url';
    case UITPAS_TEST_CLIENT_ID = 'uitpas.test.client_id';
    case UITPAS_TEST_CLIENT_SECRET = 'uitpas.test.client_secret';

    case UITPAS_PROD_OAUTH_TOKEN_URL = 'uitpas.prod.oath_token_url';
    case UITPAS_PROD_CLIENT_ID = 'uitpas.prod.client_id';
    case UITPAS_PROD_CLIENT_SECRET = 'uitpas.prod.client_secret';
}
