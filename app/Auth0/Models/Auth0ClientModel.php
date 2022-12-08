<?php

declare(strict_types=1);

namespace App\Auth0\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
