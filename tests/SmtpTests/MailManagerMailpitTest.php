<?php

declare(strict_types=1);

namespace Tests\SmtpTests;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationApproved;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Integrations\Repositories\IntegrationMailRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Domain\UdbUuid;
use App\Mails\Template\TemplateName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\GivenSubscription;
use Tests\TestCase;

final class MailManagerMailpitTest extends TestCase
{
    use GivenSubscription;
    use MailpitTester;
    use RefreshDatabase;

    private const ORG_ID = '33f1722b-04fc-4652-b99f-2c96de87cf82';
    private const BASE_URL = 'http://www.example.com';
    private const MAIL_FROM_ADDRESS = 'admin@publiq.be';
    private const MAIL_FROM_NAME = 'Mister Admin';
    private const MAIL_TO_NAME = 'John Snow';
    private const MAIL_TO_ADDRESS = 'jane@publiq.be';

    private MailManager $listener;
    private EloquentIntegrationRepository $integrationRepository;
    private EloquentUdbOrganizerRepository $udbOrganizerRepository;
    private UuidInterface $subscriptionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new MailManager(
            $this->app->get(Mailer::class),
            $this->app->get(IntegrationRepository::class),
            $this->app->get(IntegrationMailRepository::class),
            self::BASE_URL,
            self::MAIL_FROM_ADDRESS,
            self::MAIL_FROM_NAME,
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
            integrationType: IntegrationType::EntryApi
        );
    }

    #[DataProvider('mails')]
    public function testMailWasSent(
        IntegrationCreatedWithContacts|IntegrationActivated|IntegrationApproved|IntegrationActivationRequested|IntegrationDeleted|ActivationExpired $event,
        IntegrationStatus $integrationStatus,
        string $name,
        string $subject
    ): void {
        $integration = (new Integration(
            $event->id,
            IntegrationType::EntryApi,
            $name,
            'Entry API Integration description',
            $this->subscriptionId,
            $integrationStatus,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withContacts(
            new Contact(Uuid::uuid4(), $event->id, self::MAIL_TO_ADDRESS, ContactType::Contributor, 'John', 'Snow')
        )->withUdbOrganizers(
            new UdbOrganizer(Uuid::uuid4(), $event->id, new UdbUuid(self::ORG_ID), UdbOrganizerStatus::Pending),
        );

        // The events dispatched inside are not dispatched, so we manually call the handle method.
        $this->integrationRepository->save($integration);
        $this->udbOrganizerRepository->create(new UdbOrganizer(Uuid::uuid4(), $event->id, new UdbUuid(self::ORG_ID), UdbOrganizerStatus::Pending));

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
        foreach (range(1, 10) as $i) {
            $names[] = substr(uniqid('', true) . $i, 0, 10);
        }
        $i = 0;

        return [
            IntegrationCreatedWithContacts::class => [
                new IntegrationCreatedWithContacts(Uuid::uuid4()),
                IntegrationStatus::Draft,
                $names[$i],
                'Je integratie ' . $names[$i++] . ' is succesvol aangemaakt!',
            ],
            IntegrationActivated::class => [
                new IntegrationActivated(Uuid::uuid4()),
                IntegrationStatus::Draft,
                $names[$i],
                'Je integratie ' . $names[$i++] . ' is geactiveerd!',
            ],

            IntegrationApproved::class => [
                new IntegrationApproved(Uuid::uuid4()),
                IntegrationStatus::Draft,
                $names[$i],
                'Je integratie ' . $names[$i++] . ' is geactiveerd!',
            ],
            IntegrationActivationRequested::class => [
                new IntegrationActivationRequested(Uuid::uuid4()),
                IntegrationStatus::Draft,
                $names[$i],
                'Activatieaanvraag met integratie ' . $names[$i++] . '',
            ],

            IntegrationDeleted::class => [
                new IntegrationDeleted(Uuid::uuid4()),
                IntegrationStatus::Draft,
                $names[$i],
                'Integratie ' . $names[$i++] . ' is definitief verwijderd',
            ],

            ActivationExpired::class => [
                new ActivationExpired(Uuid::uuid4(), TemplateName::INTEGRATION_ACTIVATION_REMINDER),
                IntegrationStatus::Draft,
                $names[$i],
                'Hulp nodig met je Integratie ' . $names[$i++] . '?',
            ],
            ActivationExpired::class . '_final' => [
                new ActivationExpired(Uuid::uuid4(), TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER),
                IntegrationStatus::Draft,
                $names[$i],
                'Je integratie ' . $names[$i++] . ' wordt binnenkort verwijderd',
            ],
        ];
    }
}
