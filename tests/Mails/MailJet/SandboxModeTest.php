<?php

declare(strict_types=1);

namespace Tests\Mails\MailJet;

use App\Domain\Mail\Addresses;
use App\Mails\MailJet\SandboxMode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use PHPUnit\Framework\Attributes\DataProvider;

final class SandboxModeTest extends TestCase
{
    #[DataProvider('sandboxModeProvider')]
    public function test_get_sandbox_mode(bool $sandboxMode, array $allowedDomains, array $addresses, bool $expected): void
    {
        $to = new Addresses(array_map(static fn ($address) => new Address($address), $addresses));

        $this->assertSame(
            $expected,
            (new SandboxMode($sandboxMode, $allowedDomains))->forAddresses($to)
        );
    }

    public static function sandboxModeProvider(): array
    {
        $allowedDomains = ['publiq.be', 'smurfen@gmail.com'];

        return [
            'Sandbox mode is off, should return false' => [false, $allowedDomains, ['test@gmail.com'], false],
            'Sandbox mode is on, but domain is allowed, should return false' => [true, $allowedDomains, ['test@publiq.be'], false],
            'Sandbox mode is on, and domain is not allowed, should return true' => [true, $allowedDomains, ['test@verkeerd.com'], true],
            'Sandbox mode is on, but one of the addresses is from the allowed domain' => [true, $allowedDomains, ['test@publiq.be', 'test@verkeerd.com'], false],
            'Sandbox mode is on, all addresses are not from allowed domains' => [true, $allowedDomains, ['test@verkeerd.com', 'admin@verkeerd.com'], true],
            'Sandbox mode is on, entire email matches, should return false' => [true, $allowedDomains, ['smurfen@gmail.com'], false],
        ];
    }
}
