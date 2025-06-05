<?php

declare(strict_types=1);

namespace App\UiTPAS;

enum UiTiDRealms: string
{
    case TEST_INTERNAL_NAME = 'keycloak.uitid_realms.test.internalName';
    case TEST_BASE_URL = 'keycloak.uitid_realms.test.base_url';
    case TEST_CLIENT_ID = 'keycloak.uitid_realms.test.client_id';
    case TEST_CLIENT_SECRET = 'keycloak.uitid_realms.test.client_secret';

    case PROD_INTERNAL_NAME = 'keycloak.uitid_realms.prod.internalName';
    case PROD_BASE_URL = 'keycloak.uitid_realms.prod.base_url';
    case PROD_CLIENT_ID = 'keycloak.uitid_realms.prod.client_id';
    case PROD_CLIENT_SECRET = 'keycloak.uitid_realms.prod.client_secret';
}
