<?php

declare(strict_types=1);

namespace App\Auth0\Models;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class Auth0ClientModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'auth0_clients';

    protected $fillable = [
        'integration_id',
        'auth0_client_id',
        'auth0_client_secret',
        'auth0_tenant',
    ];

    public function toDomain(): Auth0Client
    {
        return new Auth0Client(
            Uuid::fromString($this->integration_id),
            $this->client_id,
            $this->client_secret,
            Auth0Tenant::from($this->auth0_tenant)
        );
    }
}
