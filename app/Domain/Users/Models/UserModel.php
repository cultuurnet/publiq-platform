<?php

declare(strict_types=1);

namespace App\Domain\Users\Models;

use App\Models\UuidModel;
use Auth0\Laravel\Contract\Model\Stateful\User as StatefulUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 */
final class UserModel extends UuidModel implements AuthenticatableContract, StatefulUser
{
    use Authenticatable;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'name',
        'email',
    ];
}
