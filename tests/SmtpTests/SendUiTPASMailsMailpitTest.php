<?php

declare(strict_types=1);

namespace Tests\SmtpTests;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Mail\Mailer;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Domain\UdbUuid;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerRejected;
use App\UiTPAS\Event\UdbOrganizerRequested;
use App\UiTPAS\Listeners\SendUiTPASMails;
use CultuurNet\SearchV3\ValueObjects\Collection;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\UrlGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Mime\Address;
use Tests\GivenSubscription;
use Tests\TestCase;

final class SendUiTPASMailsMailpitTest extends TestCase
{
    use RefreshDatabase;
    use GivenSubscription;
    use MailpitTester;

    private const ORG_ID = '33f1722b-04fc-4652-b99f-2c96de87cf82';
    private const MAIL_FROM_ADDRESS = 'admin@publiq.be';
    private const MAIL_FROM_NAME = 'Mister Admin';
    private const MAIL_TO_NAME = 'John Snow';
    private const MAIL_TO_ADDRESS = 'john@publiq.be';

    private EloquentIntegrationRepository $integrationRepository;
    private EloquentUdbOrganizerRepository $udbOrganizerRepository;
    private SendUiTPASMails $listener;
    private UuidInterface $subscriptionId;

    protected function setUp(): void
    {
        parent::setUp();

        $searchService = $this->createMock(SearchService::class);
        $searchService
            ->method('findOrganizers')
            ->with(new UdbUuid(self::ORG_ID))
            ->willReturn($this->givenUitpasOrganizers());

        $this->listener = new SendUiTPASMails(
            app(Mailer::class),
            app(IntegrationRepository::class),
            app(UdbOrganizerNameResolver::class),
            $searchService,
            app(UrlGenerator::class),
            new Address(self::MAIL_FROM_ADDRESS, self::MAIL_FROM_NAME),
        );

        $this->integrationRepository = new EloquentIntegrationRepository(
            new EloquentUdbOrganizerRepository(),
            new EloquentSubscriptionRepository(),
        );
        $this->subscriptionRepository = new EloquentSubscriptionRepository();
        $this->udbOrganizerRepository = new EloquentUdbOrganizerRepository();

        $this->subscriptionId = Uuid::uuid4();

        // This method also saves the subscription in the repository.
        $this->givenThereIsASubscription(
            id: $this->subscriptionId,
            integrationType: IntegrationType::UiTPAS
        );
    }

    #[DataProvider('mails')]
    public function testMailWasSentIntegrationCreatedWithContacts(
        IntegrationCreatedWithContacts|IntegrationActivationRequested|UdbOrganizerRequested|UdbOrganizerApproved|UdbOrganizerRejected $event,
        IntegrationStatus $integrationStatus,
        string $name,
        string $subject
    ): void {
        if ($event instanceof UdbOrganizerApproved || $event instanceof UdbOrganizerRejected || $event instanceof UdbOrganizerRequested) {
            $integrationId = $event->integrationId;
        } else {
            $integrationId = $event->id;
        }

        $integration = (new Integration(
            $integrationId,
            IntegrationType::UiTPAS,
            $name,
            'Uitpas Integration description',
            $this->subscriptionId,
            $integrationStatus,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withContacts(
            new Contact(Uuid::uuid4(), $integrationId, self::MAIL_TO_ADDRESS, ContactType::Contributor, 'John', 'Snow')
        )->withUdbOrganizers(
            new UdbOrganizer(Uuid::uuid4(), $integrationId, new UdbUuid(self::ORG_ID), UdbOrganizerStatus::Pending, Uuid::uuid4()),
        );

        // The events dispatched inside are not dispatched, so we manually call the handle method.
        $this->integrationRepository->save($integration);
        $this->udbOrganizerRepository->create(new UdbOrganizer(Uuid::uuid4(), $integrationId, new UdbUuid(self::ORG_ID), UdbOrganizerStatus::Pending, Uuid::uuid4()));

        $method = 'handle' . class_basename($event);
        $this->listener->$method($event);

        $message = $this->waitForMail(function ($mail) use ($subject) {
            return $mail['Subject'] === $subject;
        });

        $this->assertNotNull($message, 'Expected email was not received within the timeout period: ' . $subject);

        $this->assertEquals(self::MAIL_TO_NAME, $message['To'][0]['Name']);
        $this->assertEquals(self::MAIL_TO_ADDRESS, $message['To'][0]['Address']);

        $this->assertEquals(self::MAIL_FROM_NAME, $message['From']['Name']);
        $this->assertEquals(self::MAIL_FROM_ADDRESS, $message['From']['Address']);
    }

    public static function mails(): array
    {
        $names = [];
        foreach (range(1, 4) as $i) {
            $names[] = substr(uniqid('', true) . $i, 0, 10);
        }
        $i = 0;

        return [
            IntegrationActivationRequested::class => [
                new IntegrationActivationRequested(Uuid::uuid4()),
                IntegrationStatus::Draft,
                $names[$i],
                'Activatieaanvraag met integratie ' . $names[$i++] . ' voor publiq vzw!',
            ],
            UdbOrganizerRequested::class => [
                new UdbOrganizerRequested(new UdbUuid(self::ORG_ID), Uuid::uuid4()),
                IntegrationStatus::Active,
                $names[$i],
                'Activatieaanvraag met integratie ' . $names[$i++] . ' voor publiq vzw!',
            ],
            UdbOrganizerApproved::class => [
                new UdbOrganizerApproved(new UdbUuid(self::ORG_ID), Uuid::uuid4()),
                IntegrationStatus::Active,
                $names[$i],
                'Je integratie ' . $names[$i++] . ' voor publiq vzw is geactiveerd!',
            ],
            UdbOrganizerRejected::class => [
                new UdbOrganizerRejected(new UdbUuid(self::ORG_ID), Uuid::uuid4()),
                IntegrationStatus::Active,
                $names[$i],
                'Je integratie ' . $names[$i] . ' voor publiq vzw is afgekeurd!',
            ],
        ];
    }

    private function givenUitpasOrganizers(): PagedCollection
    {
        $pagedCollection = new PagedCollection();
        $org = new SapiOrganizer();
        $org->setId(self::ORG_ID);
        $org->setName(new TranslatedString(['nl' => 'publiq vzw']));
        $collection = new Collection();
        $collection->setItems([$org]);
        $pagedCollection->setMember($collection);
        $pagedCollection->setTotalItems(1);
        return $pagedCollection;
    }
}
