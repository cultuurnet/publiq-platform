<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Mail\Mailer;
use App\Domain\UdbUuid;
use App\Mails\Smtp\MailTemplate;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerRejected;
use App\UiTPAS\Event\UdbOrganizerRequested;
use App\UiTPAS\Listeners\SendUiTPASMails;
use CultuurNet\SearchV3\ValueObjects\Collection;
use CultuurNet\SearchV3\ValueObjects\Organizer;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Address;
use Tests\CreatesIntegration;
use Tests\TestCase;

final class SendUiTPASMailsTest extends TestCase
{
    use CreatesIntegration;

    private const INTEGRATION_ID = '6863edd2-f728-8010-8014-da344a5e8213';
    private const ORGANIZER_ID = '33f1722b-04fc-4652-b99f-2c96de87cf82';

    private Mailer&MockObject $mailer;
    private IntegrationRepository&MockObject $integrationRepository;
    private SearchService&MockObject $searchService;
    private UrlGeneratorContract&MockObject $urlGenerator;
    private SendUiTPASMails $handler;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(Mailer::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->searchService = $this->createMock(SearchService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorContract::class);

        $this->handler = new SendUiTPASMails(
            $this->mailer,
            $this->integrationRepository,
            new UdbOrganizerNameResolver(),
            $this->searchService,
            $this->urlGenerator,
            new Address('noreply@publiq.be', 'Publiq'),
        );
    }

    #[DataProvider('eventProvider')]
    public function test_it_sends_mail_for_valid_event(object $event, string $method, int $templateId, UdbOrganizerStatus $udbOrganizerStatus = UdbOrganizerStatus::Approved): void
    {
        $integrationId = Uuid::fromString(self::INTEGRATION_ID);
        $organizerId = new UdbUuid(self::ORGANIZER_ID);

        $integration = $this->givenThereIsAnIntegration(
            $integrationId,
            ['type' => IntegrationType::UiTPAS, 'status' => IntegrationStatus::Active],
        )
            ->withContacts(
                new Contact(Uuid::uuid4(), $integrationId, '1@publiq.be', ContactType::Contributor, 'John', 'Snow')
            )
            ->withUdbOrganizers(
                new UdbOrganizer(Uuid::uuid4(), $integrationId, $organizerId, $udbOrganizerStatus)
            );

        $this->integrationRepository
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $this->searchService
            ->method('findOrganizers')
            ->willReturn($this->givenUitpasOrganizers());

        $this->urlGenerator
            ->method('route')
            ->willReturn('https://publiq.com/nl/integrations/' . self::INTEGRATION_ID);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with(
                new Address('noreply@publiq.be', 'Publiq'),
                $this->callback(fn (Address $address) => $address->getAddress() === '1@publiq.be'),
                $templateId,
                $this->callback(fn (array $vars) => $vars['firstName'] === 'John')
            );

        $this->handler->{$method}($event);
    }

    public static function eventProvider(): array
    {
        $integrationId = Uuid::fromString(self::INTEGRATION_ID);
        $organizerId = new UdbUuid(self::ORGANIZER_ID);

        return [
            'integration_created' => [
                new IntegrationCreatedWithContacts($integrationId),
                'handleIntegrationCreatedWithContacts',
                MailTemplate::INTEGRATION_CREATED->value,
            ],
            'activation_requested' => [
                new IntegrationActivationRequested($integrationId),
                'handleIntegrationActivationRequested',
                MailTemplate::ORGANISATION_UITPAS_REQUESTED->value,
            ],
            'organizer_requested' => [
                new UdbOrganizerRequested($organizerId, $integrationId),
                'handleUdbOrganizerRequested',
                MailTemplate::ORGANISATION_UITPAS_REQUESTED->value,
                UdbOrganizerStatus::Pending,
            ],
            'organizer_approved' => [
                new UdbOrganizerApproved($organizerId, $integrationId),
                'handleUdbOrganizerApproved',
                MailTemplate::ORGANISATION_UITPAS_APPROVED->value,
            ],
            'organizer_rejected' => [
                new UdbOrganizerRejected($organizerId, $integrationId),
                'handleUdbOrganizerRejected',
                MailTemplate::ORGANISATION_UITPAS_REJECTED->value,
            ],
        ];
    }

    public function test_it_returns_early_when_type_is_not_uitpas(): void
    {
        $integration = $this->givenThereIsAnIntegration(
            Uuid::fromString(self::INTEGRATION_ID),
            ['type' => IntegrationType::EntryApi]
        );

        $this->integrationRepository
            ->method('getById')
            ->willReturn($integration);

        $this->mailer->expects($this->never())->method('send');

        $this->handler->handleIntegrationCreatedWithContacts(
            new IntegrationCreatedWithContacts(Uuid::fromString(self::INTEGRATION_ID))
        );
    }

    public function test_it_returns_early_when_integration_has_no_contacts(): void
    {
        $integration = $this->givenThereIsAnIntegration(
            Uuid::fromString(self::INTEGRATION_ID),
            ['type' => IntegrationType::UiTPAS]
        );

        $this->integrationRepository
            ->method('getById')
            ->willReturn($integration);

        $this->mailer->expects($this->never())->method('send');

        $this->handler->handleIntegrationCreatedWithContacts(
            new IntegrationCreatedWithContacts(Uuid::fromString(self::INTEGRATION_ID))
        );
    }

    private function givenUitpasOrganizers(): PagedCollection
    {
        $pagedCollection = new PagedCollection();
        $org = new Organizer();
        $org->setId(self::ORGANIZER_ID);
        $org->setName(new TranslatedString(['Mock Integration']));
        $collection = new Collection();
        $collection->setItems([$org]);
        $pagedCollection->setMember($collection);
        return $pagedCollection;
    }
}
