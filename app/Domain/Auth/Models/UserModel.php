<?php

declare(strict_types=1);

namespace App\Domain\Auth\Models;

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
final class UserModel extends Model implements AuthenticatableContract
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

    /**
     * @param array<string> $user
     */
    public static function fromSession(array $user): UserModel
    {
        return new UserModel([
            'id' => $user['sub'] ?? $user['user_id'] ?? null,
            'name' => $user['name'] ?? '',
            'email' => $user['email'],
            'first_name' => $user['https://publiq.be/first_name'] ?? '',
            'last_name' => $user['family_name'] ?? '',
        ]);
    }

    public static function createSystemUser(): UserModel
    {
        return new UserModel([
            'id' => '00000000-0000-0000-0000-000000000000',
            'name' => 'SystemUser',
            'email' => 'noreply@publiq.be',
            'first_name' => 'System',
            'last_name' => 'User',
        ]);
    }
}
