<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\CreateIntegration;
use Tests\TestCase;

final class IntegrationTest extends TestCase
{
    use CreateIntegration;

    public function testFilterUniqueContactsWithPreferredContactType(): void
    {
        $integrationId = Uuid::uuid4();
        $integration = $this->givenThereIsAnIntegration($integrationId)->withContacts(
            $this->createContact($integrationId, 'a@public.be', ContactType::Functional),
            $this->createContact($integrationId, 'a@public.be', ContactType::Contributor),
            $this->createContact($integrationId, 'b@public.be', ContactType::Contributor),
            $this->createContact($integrationId, 'c@public.be', ContactType::Functional),
            $this->createContact($integrationId, 'c@public.be', ContactType::Functional),
        );

        $result = $integration->filterUniqueContactsWithPreferredContactType(ContactType::Contributor);

        $this->assertCount(3, $result);

        $this->assertArrayHasKey('a@public.be', $result);
        $this->assertArrayHasKey('b@public.be', $result);
        $this->assertArrayHasKey('c@public.be', $result);

        $this->assertSame(ContactType::Contributor, $result['a@public.be']->type);
        $this->assertSame(ContactType::Contributor, $result['b@public.be']->type);
        $this->assertSame(ContactType::Functional, $result['c@public.be']->type);
    }

    private function createContact(UuidInterface $integrationId, string $email, ContactType $type): Contact
    {
        return new Contact(Uuid::uuid4(), $integrationId, $email, $type, 'John', 'Snow');
    }

    public function testGetUdbOrganizerByOrgId(): void
    {
        $integrationId = Uuid::uuid4();
        $orgId = new UdbUuid(Uuid::uuid4()->toString());
        $organizer = new UdbOrganizer(Uuid::uuid4(), $integrationId, $orgId, UdbOrganizerStatus::Pending, Uuid::uuid4());
        $udbOrganizer = $this->givenThereIsAnIntegration($integrationId)
            ->withUdbOrganizers($organizer);

        $result = $udbOrganizer->getUdbOrganizerByOrgId($orgId);

        $this->assertSame($organizer, $result);
    }

    public function testGetUdbOrganizerByOrgIdReturnsNull(): void
    {
        $udbOrganizer = $this->givenThereIsAnIntegration(Uuid::uuid4());

        $this->assertNull($udbOrganizer->getUdbOrganizerByOrgId(new UdbUuid(Uuid::uuid4()->toString())));
    }

    #[DataProvider('keycloakEnvironmentVisibilityProvider')]
    public function testIsKeyVisibleForKeycloakEnvironment(
        IntegrationStatus $status,
        KeyVisibility $keyVisibility,
        Environment $environment,
        bool $expected
    ): void {
        $integration = $this->givenThereIsAnIntegration(Uuid::uuid4(), ['status' => $status])
            ->withKeyVisibility($keyVisibility);

        $this->assertSame($expected, $integration->isKeyVisibleForEnvironment($environment));
    }

    public static function keycloakEnvironmentVisibilityProvider(): array
    {
        return [
            'v2 is hidden on acceptance' => [IntegrationStatus::Draft, KeyVisibility::v2, Environment::Acceptance, false],
            'v2 is visible on testing' => [IntegrationStatus::Draft, KeyVisibility::v2, Environment::Testing, true],
            'v2 is visible on production' => [IntegrationStatus::Draft, KeyVisibility::v2, Environment::Production, true],
            'all is visible on testing' => [IntegrationStatus::Draft, KeyVisibility::all, Environment::Testing, true],
            'all is visible on production' => [IntegrationStatus::Active, KeyVisibility::all, Environment::Production, true],
            'v1 is hidden on testing' => [IntegrationStatus::Draft, KeyVisibility::v1, Environment::Testing, false],
            'v1 is hidden on production' => [IntegrationStatus::Draft, KeyVisibility::v1, Environment::Production, false],
            'v1 is hidden on acceptance' => [IntegrationStatus::Draft, KeyVisibility::v1, Environment::Acceptance, false],
            'deleted hides v2 on production' => [IntegrationStatus::Deleted, KeyVisibility::v2, Environment::Production, false],
            'deleted hides v1 on acceptance' => [IntegrationStatus::Deleted, KeyVisibility::v1, Environment::Acceptance, false],
        ];
    }

}
