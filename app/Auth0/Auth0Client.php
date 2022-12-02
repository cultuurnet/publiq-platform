<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Auth0\Resources\UserResource;
use App\Json;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\API\ManagementInterface;

final class Auth0Client
{
    private ManagementInterface $management;

    public function __construct(SdkConfiguration $sdkConfiguration)
    {
        $auth0 = new Auth0($sdkConfiguration);

        if (!$auth0->configuration()->hasManagementToken()) {
            $response = $auth0->authentication()->clientCredentials($sdkConfiguration->getAudience());
            $response = Json::decodeAssociatively((string)$response->getBody());
            $auth0->configuration()->setManagementToken($response['access_token']);
        }

        $this->management = $auth0->management();
    }

    public function users(): UserResource
    {
        return new UserResource($this->management->users());
    }
}
