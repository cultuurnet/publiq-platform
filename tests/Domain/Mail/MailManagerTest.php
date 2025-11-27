<?php

declare(strict_types=1);

namespace Tests\Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationMail;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationMailRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use App\Mails\Template\MailTemplate;
use App\Mails\Template\TemplateName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Address;
use Tests\TestCase;

final class MailManagerTest extends TestCase
{
    private const INTEGRATION_ID = '9e6d778f-ef44-45b3-b842-26b6d71bcad7';

    private MailManager $mailManager;
    private Mailer&MockObject $mailer;

    /** @var Contact[] */
    private array $contacts;
    private Integration $integration;
    private IntegrationRepository&MockObject $integrationRepository;
    private IntegrationMailRepository&MockObject $integrationMailRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(Mailer::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->integrationMailRepository = $this->createMock(IntegrationMailRepository::class);

        $this->mailManager = new MailManager(
            $this->mailer,
            $this->integrationRepository,
            $this->integrationMailRepository,
            'http://www.example.com',
            'technical-support@publiq.be',
            'publiq-platform'
        );

        $this->contacts = [
            'grote.smurf@publiq.be_wrong1' => new Contact( // This contact will be discarded because the email already exists (next contact), and it is not a technical contact
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'grote.smurf@publiq.be',
                ContactType::Contributor,
                'Grote',
                'Smurf'
            ),
            'grote.smurf@publiq.be' => new Contact(
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'grote.smurf@publiq.be',
                ContactType::Technical,
                'Grote',
                'Smurf'
            ),
            'grote.smurf@publiq.be_wrong2' => new Contact( // This contact will be discarded because the email already exists, and it is not a technical contact
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'grote.smurf@publiq.be',
                ContactType::Functional,
                'Grote',
                'Smurf'
            ),
            'brilsmurf@publiq.be' => new Contact(
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'brilsmurf@publiq.be',
                ContactType::Functional,
                'Bril',
                'Smurf'
            ),
            'knutselsmurf@publiq.be' => new Contact(
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'knutselsmurf@publiq.be',
                ContactType::Contributor,
                'Knutsel',
                'Smurf'
            ),
        ];

        $this->integration = (new Integration(
            Uuid::fromString(self::INTEGRATION_ID),
            IntegrationType::SearchApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Active,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))
            ->withContacts(...$this->contacts);
    }

    public function testDoNoTSentUiTPASMailTwice(): void
    {
        $integration = (new Integration(
            Uuid::fromString(self::INTEGRATION_ID),
            IntegrationType::UiTPAS,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Active,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))
            ->withContacts(...$this->contacts);

        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($integration);

        $this->mailManager->handleIntegrationActivated(
            new IntegrationActivated(Uuid::fromString(self::INTEGRATION_ID))
        );

        $this->mailer
            ->expects($this->never())
            ->method('send');
    }

    #[DataProvider('mailDataProvider')]
    public function testSendMail(
        object $event,
        string $method,
        MailTemplate $template,
        bool $useGetByIdWithTrashed = false,
    ): void {
        $this->integrationRepository
            ->expects($this->once())
            ->method($useGetByIdWithTrashed ? 'getByIdWithTrashed' : 'getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->integration);

        $currentEmail = null;

        $this->mailer
            ->expects($this->exactly(3))
            ->method('send')
            ->with(
                new Address(config('mail.from.address'), config('mail.from.name')),
                $this->callback(function (Address $address) use (&$currentEmail) {
                    if (!isset($this->contacts[$address->getAddress()])) {
                        return false;
                    }

                    if ($address->getName() !== $this->contacts[$address->getAddress()]->firstName . ' ' . $this->contacts[$address->getAddress()]->lastName) {
                        return false;
                    }

                    $currentEmail = $address->getAddress();

                    return true;
                }),
                $template,
                // Because with() is called with all callbacks at the same time, we have to pass currentEmail as reference
                $this->callback(function ($parameters) use (&$currentEmail) {
                    $this->assertEquals([
                        'url' => 'http://www.example.com/nl/integraties/' . self::INTEGRATION_ID,
                        'integrationName' => 'Mock Integration',
                        'type' => 'search-api',
                        'firstName' => $this->contacts[$currentEmail]->firstName,
                        'lastName' => $this->contacts[$currentEmail]->lastName,
                        'contactType' => $this->contacts[$currentEmail]->type->value,
                        'showContentCheck' => false,
                    ], $parameters);

                    return true;
                })
            );

        $this->integrationMailRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (IntegrationMail $integrationMail) use ($template) {
                return $integrationMail->templateName === $template->name &&
                    $integrationMail->integrationId->toString() === self::INTEGRATION_ID;
            }));

        $this->mailManager->$method($event);
    }

    public static function mailDataProvider(): array
    {
        return [
            TemplateName::INTEGRATION_CREATED->value => [
                'event' => new IntegrationCreatedWithContacts(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'handleIntegrationCreatedWithContacts',
                'template' => new MailTemplate(TemplateName::INTEGRATION_CREATED, IntegrationType::SearchApi),
            ],
            TemplateName::INTEGRATION_ACTIVATED->value => [
                'event' => new IntegrationActivated(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'handleIntegrationActivated',
                'template' => new MailTemplate(TemplateName::INTEGRATION_ACTIVATED, IntegrationType::SearchApi),
            ],
            TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
                'event' => new IntegrationActivationRequested(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'handleIntegrationActivationRequested',
                'template' => new MailTemplate(TemplateName::INTEGRATION_ACTIVATION_REQUEST, IntegrationType::SearchApi),
            ],
            TemplateName::INTEGRATION_DELETED->value => [
                'event' => new IntegrationDeleted(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'handleIntegrationDeleted',
                'template' => new MailTemplate(TemplateName::INTEGRATION_DELETED, IntegrationType::SearchApi),
                'useGetByIdWithTrashed' => true,
            ],
            TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
                'event' => new ActivationExpired(
                    Uuid::fromString(self::INTEGRATION_ID),
                    TemplateName::INTEGRATION_ACTIVATION_REMINDER
                ),
                'method' => 'handleActivationExpired',
                'template' => new MailTemplate(TemplateName::INTEGRATION_ACTIVATION_REMINDER, IntegrationType::SearchApi),
            ],
            TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER->value => [
                'event' => new ActivationExpired(
                    Uuid::fromString(self::INTEGRATION_ID),
                    TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER
                ),
                'method' => 'handleActivationExpired',
                'template' => new MailTemplate(TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER, IntegrationType::SearchApi),
            ],
        ];
    }
}
