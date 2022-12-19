<?php

namespace Publiq\ClientCredentials;

use Laravel\Nova\ResourceTool;

class ClientCredentials extends ResourceTool
{
    public function name(): string
    {
        return 'Client Credentials';
    }

    public function component(): string
    {
        return 'client-credentials';
    }
}
