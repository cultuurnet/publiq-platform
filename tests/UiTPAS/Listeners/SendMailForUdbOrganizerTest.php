<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Mail\Mailer;
use App\Domain\UdbUuid;
use App\Mails\Smtp\MailerTemplate;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerRejected;
use App\UiTPAS\Listeners\SendMailForUdbOrganizer;
use CultuurNet\SearchV3\ValueObjects\Collection;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Address;
use Tests\CreatesIntegration;
use Tests\TestCase;

final class SendMailForUdbOrganizerTest extends TestCase
{
    use CreatesIntegration;
    private const INTEGRATION_ID = '6863edd2-f728-8010-8014-da344a5e8213';

    private Mailer&MockObject $mailer;
    private UdbOrganizerRepository&MockObject $udbOrganizerRepository;
    private IntegrationRepository&MockObject $integrationRepository;
    private UdbOrganizerNameResolver $nameResolver;
    private SearchService&MockObject $searchService;
    private UrlGeneratorContract&MockObject $urlGenerator;
    private SendMailForUdbOrganizer $handler;
    private Integration $integration;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(Mailer::class);
        $this->udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->nameResolver = new UdbOrganizerNameResolver();
        $this->searchService = $this->createMock(SearchService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorContract::class);

        $uuid = Uuid::fromString(self::INTEGRATION_ID);
        $this->integration = $this->givenThereIsAnIntegration($uuid)
            ->withContacts(
                new Contact(Uuid::uuid4(), $uuid, '1@publiq.be', ContactType::Contributor, 'John', 'Snow')
            );

        $this->handler = new SendMailForUdbOrganizer(
            $this->mailer,
            $this->udbOrganizerRepository,
            $this->integrationRepository,
            $this->nameResolver,
            $this->searchService,
            $this->urlGenerator,
            new Address('noreply@publiq.be', 'Publiq')
        );
    }

    public function testItSendsMailOnUdbOrganizerCreated(): void
    {
        $udbOrganizer = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::fromString(self::INTEGRATION_ID),
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Pending
        );

        $event = new UdbOrganizerCreated($udbOrganizer->id);

        $this->udbOrganizerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($udbOrganizer->id)
            ->willReturn($udbOrganizer);

        $this->mockCommonSendMailFlow($udbOrganizer->organizerId, MailerTemplate::ORGANISATION_UITPAS_REQUESTED->value);

        $this->handler->handleUdbOrganizerCreated($event);
    }

    public function testItSendsMailOnUdbOrganizerApproved(): void
    {
        $udbId = new UdbUuid(Uuid::uuid4()->toString());

        $this->mockCommonSendMailFlow($udbId, MailerTemplate::ORGANISATION_UITPAS_APPROVED->value);

        $this->handler->handleUdbOrganizerApproved(new UdbOrganizerApproved($udbId, Uuid::fromString(self::INTEGRATION_ID)));
    }

    public function testItSendsMailOnUdbOrganizerRejected(): void
    {
        $udbId = new UdbUuid(Uuid::uuid4()->toString());

        $this->mockCommonSendMailFlow($udbId, MailerTemplate::ORGANISATION_UITPAS_REJECTED->value);

        $this->handler->handleUdbOrganizerRejected(new UdbOrganizerRejected($udbId, Uuid::fromString(self::INTEGRATION_ID)));
    }

    private function mockCommonSendMailFlow(UdbUuid $udbId, int $templateId): void
    {
        $integrationId = Uuid::fromString(self::INTEGRATION_ID);

        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($this->integration);

        $this->searchService
            ->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with($udbId)
            ->willReturn($this->givenUitpasOrganizers());

        $this->urlGenerator
            ->expects($this->once())
            ->method('route')
            ->with('nl.integrations.show', $integrationId)
            ->willReturn('https://publiq.com/nl/integrations/' . $integrationId->toString());

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with(
                new Address('noreply@publiq.be', 'Publiq'),
                $this->callback(function (Address $address) {
                    return $address->getAddress() === '1@publiq.be'
                        && $address->getName() === 'John Snow';
                }),
                $templateId,
                $this->callback(function (array $vars) {
                    return $vars['firstName'] === 'John'
                        && $vars['integrationName'] === 'Mock Integration';
                })
            );
    }

    private function givenUitpasOrganizers(): PagedCollection
    {
        $pagedCollection = new PagedCollection();
        $org = new SapiOrganizer();
        $org->setId('33f1722b-04fc-4652-b99f-2c96de87cf82');
        $org->setName(new TranslatedString(['Test Org']));
        $collection = new Collection();
        $collection->setItems([$org]);
        $pagedCollection->setMember($collection);
        return $pagedCollection;
    }
}
