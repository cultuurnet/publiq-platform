<?php

declare(strict_types=1);

namespace App\Auth0\Resources;

use App\Json;
use Auth0\SDK\Contract\API\Management\UsersInterface;
use Illuminate\Support\Collection;

final class UserResource
{
    public function __construct(private readonly UsersInterface $users)
    {
    }

    public function searchUsersByEmail(string $email): Collection
    {
        $response = $this->users->getAll(['q' => 'email:*' . $email . '*']);
        return new Collection(Json::decodeAssociatively((string) $response->getBody()));
    }
}
