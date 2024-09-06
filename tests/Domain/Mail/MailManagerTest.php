<?php

declare(strict_types=1);

namespace Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Addresses;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Address;
use Tests\TestCase;

final class MailManagerTest extends TestCase
{
    private const INTEGRATION_ID = '9e6d778f-ef44-45b3-b842-26b6d71bcad7';
    private const TEMPLATE_BLOCKED_ID = 456;
    private const TEMPLATE_ACTIVATED_ID = 123;
    private const TEMPLATE_CREATED_ID = 677;
    private MailManager $mailManager;
    private Mailer&MockObject $mailer;

    /** @var Contact[] */
    private array $contacts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(Mailer::class);
        $integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->mailManager = new MailManager(
            $this->mailer,
            $integrationRepository,
            self::TEMPLATE_CREATED_ID,
            self::TEMPLATE_ACTIVATED_ID,
            self::TEMPLATE_BLOCKED_ID,
            'http://www.example.com'
        );

        $this->contacts = [
            new Contact(
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'grote.smurf@publiq.be',
                ContactType::Technical,
                'Grote',
                'Smurf'
            ),
            new Contact(
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'brilsmurf@publiq.be',
                ContactType::Functional,
                'Bril',
                'Smurf'
            ),
            new Contact(
                Uuid::uuid4(),
                Uuid::fromString(self::INTEGRATION_ID),
                'knutselsmurf@publiq.be',
                ContactType::Contributor,
                'Knutsel',
                'Smurf'
            ),
        ];

        $integration = (new Integration(
            Uuid::fromString(self::INTEGRATION_ID),
            IntegrationType::SearchApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Active,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))
            ->withContacts(...$this->contacts);

        $integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($integration);
    }

    /**
     * @dataProvider mailDataProvider
     */
    public function testSendMail(
        object $event,
        string $method,
        int $templateId,
        string $subject,
        array $expectedParameters
    ): void {
        $counter = 0;

        $this->mailer
            ->expects($this->exactly(count($this->contacts)))
            ->method('send')
            ->with(
                new Address(config('mail.from.address'), config('mail.from.name')),
                $this->callback(function (Addresses $addresses) use (&$counter) {
                    /** @var Address $address */
                    $address = $addresses->first();
                    if ($address->getAddress() !== $this->contacts[$counter]->email) {
                        return false;
                    }

                    if ($address->getName() !== $this->contacts[$counter]->firstName . ' ' . $this->contacts[$counter]->lastName) {
                        return false;
                    }

                    return true;
                }),
                $templateId,
                $subject,
                $this->callback(function ($parameters) use ($expectedParameters, &$counter) {
                    $expectedParameters['firstName'] = $this->contacts[$counter]->firstName;
                    $expectedParameters['lastName'] = $this->contacts[$counter]->lastName;
                    $expectedParameters['contactType'] = $this->contacts[$counter]->type->value;

                    $this->assertEquals($expectedParameters, $parameters);

                    $counter++;

                    return true;
                })
            );

        $this->mailManager->$method($event);
    }

    public static function mailDataProvider(): array
    {
        return [
            'IntegrationCreated' => [
                'event' => new IntegrationCreatedWithContacts(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationCreatedMail',
                'templateId' => self::TEMPLATE_CREATED_ID,
                'subject' => 'Welcome to Publiq platform - Let\'s get you started!',
                'expectedParameters' => [
                    'url' => 'http://www.example.com/nl/integraties/' . self::INTEGRATION_ID,
                    'integrationName' => 'Mock Integration',
                    'type' => 'search-api',
                ],
            ],
            'IntegrationActivated' => [
                'event' => new IntegrationActivated(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationActivatedMail',
                'templateId' => self::TEMPLATE_ACTIVATED_ID,
                'subject' => 'Publiq platform - Integration activated',
                'expectedParameters' => [
                    'url' => 'http://www.example.com/nl/integraties/' . self::INTEGRATION_ID,
                    'integrationName' => 'Mock Integration',
                    'type' => 'search-api',
                ],
            ],
            'IntegrationBlocked' => [
                'event' => new IntegrationBlocked(Uuid::fromString(self::INTEGRATION_ID)),
                'method' => 'sendIntegrationBlockedMail',
                'templateId' => self::TEMPLATE_BLOCKED_ID,
                'subject' => 'Publiq platform - Integration blocked',
                'expectedParameters' => [
                    'integrationName' => 'Mock Integration',
                ],
            ],
        ];
    }
}
