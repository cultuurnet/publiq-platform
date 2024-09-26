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
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use App\Mails\Template\TemplateName;
use App\Mails\Template\Templates;
use Carbon\Carbon;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(Mailer::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->mailManager = new MailManager(
            $this->mailer,
            $this->integrationRepository,
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
        int $templateId,
        string $subject,
        bool $checkReminderEmailSent = false,
        bool $useGetByIdWithTrashed = false,
    ): void {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->integrationRepository
            ->expects($this->once())
            ->method($useGetByIdWithTrashed ? 'getByIdWithTrashed' : 'getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->integration);

        if ($checkReminderEmailSent) {
            if (!$event instanceof ActivationExpired) {
                $this->fail(sprintf('Invalid event %s, expected ActivationExpired', get_class($event)));
            }

            $this->integrationRepository
                ->expects($this->once())
                ->method('updateReminderEmailSent')
                ->with(self::INTEGRATION_ID, $event->templateName, $now);
        }

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
                $templateId,
                $subject,
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

        $this->mailManager->$method($event);
    }

    public static function mailDataProvider(): array
    {
        return [
            TemplateName::INTEGRATION_CREATED->value => [
                'event' => new IntegrationCreatedWithContacts(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationCreatedMail',
                'templateId' => self::TEMPLATE_CREATED_ID,
                'subject' => 'Welcome to Publiq platform - Let\'s get you started!',
            ],
            TemplateName::INTEGRATION_ACTIVATED->value => [
                'event' => new IntegrationActivated(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationActivatedMail',
                'templateId' => self::TEMPLATE_ACTIVATED_ID,
                'subject' => 'Publiq platform - Integration activated',
            ],
            TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
                'event' => new IntegrationActivationRequested(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationActivationRequestMail',
                'templateId' => self::TEMPLATE_ACTIVATION_REQUESTED_ID,
                'subject' => 'Publiq platform - Request for activating integration',
            ],
            TemplateName::INTEGRATION_DELETED->value => [
                'event' => new IntegrationDeleted(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationDeletedMail',
                'templateId' => self::TEMPLATE_DELETED_ID,
                'subject' => 'Publiq platform - Integration deleted',
                'useGetByIdWithTrashed' => true,
            ],
            TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
                'event' => new ActivationExpired(
                    Uuid::fromString(self::INTEGRATION_ID),
                    TemplateName::INTEGRATION_ACTIVATION_REMINDER
                ),
                'method' => 'sendActivationReminderEmail',
                'templateId' => self::TEMPLATE_INTEGRATION_ACTIVATION_REMINDER,
                'subject' => 'Publiq platform - Can we help you to activate your integration?',
                'checkReminderEmailSent' => true,
            ],
            TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER->value => [
                'event' => new ActivationExpired(
                    Uuid::fromString(self::INTEGRATION_ID),
                    TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER
                ),
                'method' => 'sendActivationReminderEmail',
                'templateId' => self::TEMPLATE_INTEGRATION_FINAL_ACTIVATION_REMINDER,
                'subject' => 'Publiq platform - Can we help you to activate your integration?',
                'checkReminderEmailSent' => true,
            ],
        ];
    }

    private function getTemplateConfig(): array
    {
        return [
            TemplateName::INTEGRATION_CREATED->value => [
                'id' => self::TEMPLATE_CREATED_ID,
                'enabled' => true,
                'subject' => 'Welcome to Publiq platform - Let\'s get you started!',
            ],
            TemplateName::INTEGRATION_ACTIVATED->value => [
                'id' => self::TEMPLATE_ACTIVATED_ID,
                'enabled' => true,
                'subject' => 'Publiq platform - Integration activated',
            ],
            TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
                'id' => self::TEMPLATE_INTEGRATION_ACTIVATION_REMINDER,
                'enabled' => true,
                'subject' => 'Publiq platform - Can we help you to activate your integration?',
            ],
            TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER->value => [
                'id' => self::TEMPLATE_INTEGRATION_FINAL_ACTIVATION_REMINDER,
                'enabled' => true,
                'subject' => 'Publiq platform - Can we help you to activate your integration?',
            ],
            TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
                'id' => self::TEMPLATE_ACTIVATION_REQUESTED_ID,
                'enabled' => true,
                'subject' => 'Publiq platform - Request for activating integration',
            ],
            TemplateName::INTEGRATION_DELETED->value => [
                'id' => self::TEMPLATE_DELETED_ID,
                'enabled' => true,
                'subject' => 'Publiq platform - Integration deleted',
            ],
        ];
    }
}
