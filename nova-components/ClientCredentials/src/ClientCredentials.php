<?php

namespace Publiq\ClientCredentials;

use Laravel\Nova\ResourceTool;

class ClientCredentials extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Client Credentials';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'client-credentials';
    }
}
