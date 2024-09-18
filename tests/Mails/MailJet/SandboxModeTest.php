<?php

declare(strict_types=1);

namespace Tests\Mails\MailJet;

use App\Mails\MailJet\SandboxMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

final class SandboxModeTest extends TestCase
{
    #[DataProvider('sandboxModeProvider')]
    public function test_get_sandbox_mode(bool $sandboxMode, array $allowedDomains, Address $to, bool $expected): void
    {
        $this->assertSame(
            $expected,
            (new SandboxMode($sandboxMode, $allowedDomains))->forAddress($to)
        );
    }

    public static function sandboxModeProvider(): array
    {
        $allowedDomains = ['publiq.be', 'smurfen@gmail.com'];

        return [
            'Sandbox mode is off and domain is not allowed, should return false' => [
                false,
                $allowedDomains,
                new Address('test@gmail.com'),
                false,
            ],
            'Sandbox mode is off and domain is allowed, should return false' => [
                false,
                $allowedDomains,
                new Address('test@publiq.be'),
                false,
            ],
            'Sandbox mode is on, but domain is allowed, should return false' => [
                true,
                $allowedDomains,
                new Address('test@publiq.be'),
                false,
            ],
            'Sandbox mode is on, and domain is not allowed, should return true' => [
                true,
                $allowedDomains,
                new Address('test@verkeerd.com'),
                true,
            ],
            'Sandbox mode is on, entire email matches, should return false' => [
                true,
                $allowedDomains,
                new Address('smurfen@gmail.com'),
                false,
            ],
        ];
    }
}
