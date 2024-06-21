<?php

declare(strict_types=1);

namespace Tests\Domain\Auth\AuthenticationStrategy\Mock;

use Illuminate\Session\SessionManager;

/** Using a class mock to
 * A) work around facade with magic methods
 * B) have actual get/put logic working
 */
final class SessionManagerMock extends SessionManager
{
    private array $values;

    public function __construct()
    {

    }

    public function put(string $key, mixed $value = null): void
    {
        $this->values[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }
}
