<?php

declare(strict_types=1);

namespace Tests\Domain\Auth\Models;

use App\Domain\Auth\Models\UserModel;
use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{
    public function test_from_session_with_all_values(): void
    {
        $user = UserModel::fromSession([
            'sub' => 'e9f3594e-8bbf-4099-aa2c-6552b3927433',
            'name' => 'John Doe',
            'email' => 'john.doe@anonymous.com',
            'https://publiq.be/first_name' => 'John',
            'family_name' => 'Doe',
        ]);

        $this->assertSame('e9f3594e-8bbf-4099-aa2c-6552b3927433', $user->id);
        $this->assertSame('John Doe', $user->name);
        $this->assertSame('john.doe@anonymous.com', $user->email);
        $this->assertSame('John', $user->first_name);
        $this->assertSame('Doe', $user->last_name);
    }

    public function test_from_session_user_id_fallback(): void
    {
        $user = UserModel::fromSession([
            'user_id' => 'e9f3594e-8bbf-4099-aa2c-6552b3927433',
            'name' => 'John Doe',
            'email' => 'john.doe@anonymous.com',
            'https://publiq.be/first_name' => 'John',
            'family_name' => 'Doe',
        ]);

        $this->assertSame('e9f3594e-8bbf-4099-aa2c-6552b3927433', $user->id);
        $this->assertSame('John Doe', $user->name);
        $this->assertSame('john.doe@anonymous.com', $user->email);
        $this->assertSame('John', $user->first_name);
        $this->assertSame('Doe', $user->last_name);
    }

    public function test_from_session_with_only_sub_and_email(): void
    {
        $user = UserModel::fromSession([
            'sub' => 'e9f3594e-8bbf-4099-aa2c-6552b3927433',
            'email' => 'john.doe@anonymous.com',
        ]);

        $this->assertSame('e9f3594e-8bbf-4099-aa2c-6552b3927433', $user->id);
        $this->assertSame('', $user->name);
        $this->assertSame('john.doe@anonymous.com', $user->email);
        $this->assertSame('', $user->first_name);
        $this->assertSame('', $user->last_name);
    }
}
