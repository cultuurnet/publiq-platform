<?php

declare(strict_types=1);

namespace App\Domain\Users\Models;

use App\Models\UuidModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

final class UserModel extends UuidModel implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'user';
}
