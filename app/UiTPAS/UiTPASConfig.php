<?php

declare(strict_types=1);

namespace App\UiTPAS;

enum UiTPASConfig: string
{
    case REALM_NAME = 'uitid';

    case AUTOMATIC_PERMISSIONS_ENABLED = 'uitpas.automatic_permissions_enabled';
    case CLIENT_PERMISSIONS_URI = 'uitpas.client_permissions_uri';
    case UDB_BASE_URI = 'uitpas.udb_base_uri';

    case TEST_ORGANISATION = 'uitpas.test.organisation';
    case TEST_API_ENDPOINT = 'uitpas.test.api_endpoint';
    case PROD_API_ENDPOINT = 'uitpas.prod.api_endpoint';

    case TEST_OAUTH_TOKEN_URL = 'uitpas.test.oauth_token_url';
    case TEST_CLIENT_ID = 'uitpas.test.client_id';
    case TEST_CLIENT_SECRET = 'uitpas.test.client_secret';

    case PROD_OAUTH_TOKEN_URL = 'uitpas.prod.oauth_token_url';
    case PROD_CLIENT_ID = 'uitpas.prod.client_id';
    case PROD_CLIENT_SECRET = 'uitpas.prod.client_secret';
}
