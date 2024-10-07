<?php

declare(strict_types=1);

namespace Tests\Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationApproved;
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
use App\Mails\Template\Template;
use App\Mails\Template\TemplateName;
use App\Mails\Template\Templates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Address;
use Tests\TestCase;

final class MailManagerTest extends TestCase
{
    private const INTEGRATION_ID = '9e6d778f-ef44-45b3-b842-26b6d71bcad7';
    private const TEMPLATE_ACTIVATED_ID = 2;
    private const TEMPLATE_CREATED_ID = 3;
    private const TEMPLATE_INTEGRATION_ACTIVATION_REMINDER = 4;
    private const TEMPLATE_ACTIVATION_REQUESTED_ID = 5;
    private const TEMPLATE_DELETED_ID = 6;
    private const TEMPLATE_INTEGRATION_FINAL_ACTIVATION_REMINDER = 7;

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
            Templates::build($this->getTemplateConfig()),
            'http://www.example.com'
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

    #[DataProvider('mailDataProvider')]
    public function testSendMail(
        object $event,
        string $method,
        Template $template,
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
                $template->id,
                // Because with() is called with all callbacks at the same time, we have to pass currentEmail as reference
                $this->callback(function ($parameters) use (&$currentEmail) {
                    $this->assertEquals([
                        'url' => 'http://www.example.com/nl/integraties/' . self::INTEGRATION_ID,
                        'integrationName' => 'Mock Integration',
                        'type' => 'search-api',
                        'firstName' => $this->contacts[$currentEmail]->firstName,
                        'lastName' => $this->contacts[$currentEmail]->lastName,
                        'contactType' => $this->contacts[$currentEmail]->type->value,
                    ], $parameters);

                    return true;
                })
            );

        $this->integrationMailRepository
            ->expects($this->once())
            ->method('create')
            ->with(new IntegrationMail(
                Uuid::fromString(self::INTEGRATION_ID),
                $template->type,
            ));

        $this->mailManager->$method($event);
    }

    public static function mailDataProvider(): array
    {
        return [
            TemplateName::INTEGRATION_CREATED->value => [
                'event' => new IntegrationCreatedWithContacts(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationCreatedMail',
                'template' => new Template(TemplateName::INTEGRATION_CREATED, self::TEMPLATE_CREATED_ID),
            ],
            TemplateName::INTEGRATION_ACTIVATED->value => [
                'event' => new IntegrationActivated(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationActivatedMail',
                'template' => new Template(TemplateName::INTEGRATION_ACTIVATED, self::TEMPLATE_ACTIVATED_ID),
            ],
            'integration_approved' => [
                'event' => new IntegrationApproved(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationApprovedMail',
                'template' => new Template(TemplateName::INTEGRATION_ACTIVATED, self::TEMPLATE_ACTIVATED_ID),
            ],
            TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
                'event' => new IntegrationActivationRequested(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationActivationRequestMail',
                'template' => new Template(TemplateName::INTEGRATION_ACTIVATION_REQUEST, self::TEMPLATE_ACTIVATION_REQUESTED_ID),
            ],
            TemplateName::INTEGRATION_DELETED->value => [
                'event' => new IntegrationDeleted(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationDeletedMail',
                'template' => new Template(TemplateName::INTEGRATION_DELETED, self::TEMPLATE_DELETED_ID),
                'useGetByIdWithTrashed' => true,
            ],
            TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
                'event' => new ActivationExpired(
                    Uuid::fromString(self::INTEGRATION_ID),
                    TemplateName::INTEGRATION_ACTIVATION_REMINDER
                ),
                'method' => 'sendActivationReminderEmail',
                'template' => new Template(TemplateName::INTEGRATION_ACTIVATION_REMINDER, self::TEMPLATE_INTEGRATION_ACTIVATION_REMINDER),
            ],
            TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER->value => [
                'event' => new ActivationExpired(
                    Uuid::fromString(self::INTEGRATION_ID),
                    TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER
                ),
                'method' => 'sendActivationReminderEmail',
                'template' => new Template(TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER, self::TEMPLATE_INTEGRATION_FINAL_ACTIVATION_REMINDER),
            ],
        ];
    }

    private function getTemplateConfig(): array
    {
        return [
            TemplateName::INTEGRATION_CREATED->value => [
                'id' => self::TEMPLATE_CREATED_ID,
                'enabled' => true,
            ],
            TemplateName::INTEGRATION_ACTIVATED->value => [
                'id' => self::TEMPLATE_ACTIVATED_ID,
                'enabled' => true,
            ],
            TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
                'id' => self::TEMPLATE_INTEGRATION_ACTIVATION_REMINDER,
                'enabled' => true,
            ],
            TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER->value => [
                'id' => self::TEMPLATE_INTEGRATION_FINAL_ACTIVATION_REMINDER,
                'enabled' => true,
            ],
            TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
                'id' => self::TEMPLATE_ACTIVATION_REQUESTED_ID,
                'enabled' => true,
            ],
            TemplateName::INTEGRATION_DELETED->value => [
                'id' => self::TEMPLATE_DELETED_ID,
                'enabled' => true,
            ],
        ];
    }
}
