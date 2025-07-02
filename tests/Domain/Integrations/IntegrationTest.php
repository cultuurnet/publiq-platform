<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
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
        $this->assertSame(ContactType::Contributor, $result['a@public.be']->type);
        $this->assertArrayHasKey('b@public.be', $result);
        $this->assertArrayHasKey('c@public.be', $result);
    }

    private function createContact(UuidInterface $integrationId, string $email, ContactType $type): Contact
    {
        return new Contact(Uuid::uuid4(), $integrationId, $email, $type, 'John', 'Snow');
    }
}
