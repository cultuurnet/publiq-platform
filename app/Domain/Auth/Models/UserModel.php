<?php

declare(strict_types=1);

namespace App\Domain\Auth\Models;

use Auth0\Laravel\Contract\Model\Stateful\User as StatefulUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $email
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 */
final class UserModel extends Model implements AuthenticatableContract, StatefulUser
{
    use Authenticatable;

    protected $fillable = [
        'id',
        'email',
        'name',
        'first_name',
        'last_name',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
