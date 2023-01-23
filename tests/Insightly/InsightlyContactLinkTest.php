<?php

declare(strict_types=1);

namespace Tests\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Insightly\InsightlyContactLink;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class InsightlyContactLinkTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyContactLink $contactLink;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->contactLink = new InsightlyContactLink($this->insightlyClient);
    }

    public function test_it_creates_a_new_contact_when_the_email_is_unique(): void
    {
        $email = 'info@publiq.be';
        $insightlyId = 42;

        $contact = $this->buildContactWithEmail($email);

        $this->givenTheContactIdsFoundByEmailAre($email, []);

        $this->contactResource->expects($this->once())
            ->method('create')
            ->with($contact)
            ->willReturn($insightlyId);

        $linkedId = $this->contactLink->link($contact);

        $this->assertEquals($insightlyId, $linkedId);
    }

    public function test_it_returns_the_id_of_the_contact_with_the_same_email(): void
    {
        $email = 'info@publiq.be';
        $insightlyId = 42;

        $contact = $this->buildContactWithEmail($email);

        $this->givenTheContactIdsFoundByEmailAre($email, [$insightlyId]);

        $this->contactResource->expects($this->never())
            ->method('create');

        $linkedId = $this->contactLink->link($contact);

        $this->assertEquals($insightlyId, $linkedId);
    }

    public function test_it_returns_the_lowest_id_when_multiple_contacts_have_the_same_email(): void
    {
        $email = 'info@publiq.be';
        $insightlyId = 42;
        $foundContactIds = [52, 136, 68, $insightlyId, 124, 88, 99];

        $contact = $this->buildContactWithEmail($email);

        $this->givenTheContactIdsFoundByEmailAre($email, $foundContactIds);

        $this->contactResource->expects($this->never())
            ->method('create');

        $linkedId = $this->contactLink->link($contact);

        $this->assertEquals($insightlyId, $linkedId);
    }

    private function givenTheContactIdsFoundByEmailAre(string $email, array $contactIds): void
    {
        $this->contactResource->expects($this->once())
            ->method('findIdsByEmail')
            ->with($email)
            ->willReturn($contactIds);
    }

    private function buildContactWithEmail(string $email): Contact
    {
        return new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            $email,
            ContactType::Functional,
            'Jane',
            'Doe'
        );
    }
}
