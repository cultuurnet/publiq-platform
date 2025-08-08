<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
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
        $organizer = new UdbOrganizer(Uuid::uuid4(), $integrationId, $orgId, UdbOrganizerStatus::Pending);
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
}
