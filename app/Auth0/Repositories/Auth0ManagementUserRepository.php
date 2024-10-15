<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

use App\Domain\Auth\Repositories\UserRepository;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\API\ManagementInterface;

final readonly class Auth0ManagementUserRepository implements UserRepository
{
    private ManagementInterface $management;

    public function __construct(SdkConfiguration $sdkConfiguration)
    {
        $auth0 = new Auth0($sdkConfiguration);
        $this->management = $auth0->management();
    }

    public function findUserIdByEmail(string $email): ?string
    {
        $response = $this->management->users()->getAll(['q' => $email]);
        $json = json_decode($response->getBody()->getContents());

        if (!is_array($json) || count($json) === 0) {
            return null;
        }

        return $json[0]->user_id;
    }
}
