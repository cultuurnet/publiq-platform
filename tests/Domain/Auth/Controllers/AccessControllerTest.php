<?php

declare(strict_types=1);

namespace Tests\Domain\Auth\Controllers;

use App\Domain\Auth\Controllers\AccessController;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Json;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\Auth0Interface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class AccessControllerTest extends TestCase
{
    private Auth0Interface & MockObject $auth0;
    private IntegrationRepository & MockObject $integrationRepository;
    private LoggerInterface & MockObject $logger;

    private AccessController $accessController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth0 = $this->createMock(Auth0Interface::class);
        $this->auth0->expects($this->once())
            ->method('configuration')
            ->willReturn(new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                domain: 'mock-acc.auth0.com',
                audience: ['https://mock.auth0.com/api/v2/'],
                managementToken: 'mock-token',
            ));

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->accessController = new AccessController(
            $this->auth0,
            $this->integrationRepository,
            ['admin@publiq.be'],
            $this->logger
        );
    }

    public function test_access_with_invalid_token_fails(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Requested IntegrationAccess for integration 9469af87-3455-4853-a156-dbbc24965a26');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Invalid token', ['exception' => 'The JWT string must contain two dots']);

        $this->integrationRepository->expects($this->never())
            ->method('getById');

        $response = $this->accessController->handle('invalid token', '9469af87-3455-4853-a156-dbbc24965a26');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['exception' => 'The JWT string must contain two dots'],
            Json::decodeAssociatively((string) $response->getContent())
        );
    }

    public function test_access_with_token_with_missing_email_fails(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Requested IntegrationAccess for integration 9469af87-3455-4853-a156-dbbc24965a26');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('No email in token');

        $this->integrationRepository->expects($this->never())
            ->method('getById');

        $response = $this->accessController->handle(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
            '9469af87-3455-4853-a156-dbbc24965a26'
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['exception' => 'No email in token'],
            Json::decodeAssociatively((string) $response->getContent())
        );
    }

    public function test_admin_has_full_access(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->with(
                $this->logicalOr(
                    'Requested IntegrationAccess for integration 9469af87-3455-4853-a156-dbbc24965a26',
                    'Admin access for admin@publiq.be'
                )
            );

        $this->integrationRepository->expects($this->never())
            ->method('getById');

        $response = $this->accessController->handle(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJhZG1pbkBwdWJsaXEuYmUiLCJpYXQiOjE1MTYyMzkwMjJ9.Haq7nxdrHejchsa22_JFUevoYF_vpEFBjW9iSdN2CF4',
            '9469af87-3455-4853-a156-dbbc24965a26'
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_known_contact_has_access(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->with(
                $this->logicalOr(
                    'Requested IntegrationAccess for integration 9469af87-3455-4853-a156-dbbc24965a26',
                    'IntegrationAccess for '
                )
            );

        $integration = (new Integration(
            Uuid::fromString('9469af87-3455-4853-a156-dbbc24965a26'),
            IntegrationType::Widgets,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withContacts(new Contact(
            Uuid::uuid4(),
            Uuid::fromString('9469af87-3455-4853-a156-dbbc24965a26'),
            'john.doe@anonymous.be',
            ContactType::Contributor,
            'John',
            'Doe'
        ));

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with(Uuid::fromString('9469af87-3455-4853-a156-dbbc24965a26'))
            ->willReturn($integration);

        $response = $this->accessController->handle(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huLmRvZUBhbm9ueW1vdXMuYmUiLCJpYXQiOjE1MTYyMzkwMjJ9.tQ2AXXOyzvTzoVHkE-41lhVlbnK-0F-9LcZjUu8clyw',
            '9469af87-3455-4853-a156-dbbc24965a26'
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_unknown_contact_has_no_access(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->with(
                $this->logicalOr(
                    'Requested IntegrationAccess for integration 9469af87-3455-4853-a156-dbbc24965a26',
                    'IntegrationAccess for '
                )
            );

        $integration = new Integration(
            Uuid::fromString('9469af87-3455-4853-a156-dbbc24965a26'),
            IntegrationType::Widgets,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with(Uuid::fromString('9469af87-3455-4853-a156-dbbc24965a26'))
            ->willReturn($integration);

        $response = $this->accessController->handle(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huLmRvZUBhbm9ueW1vdXMuYmUiLCJpYXQiOjE1MTYyMzkwMjJ9.tQ2AXXOyzvTzoVHkE-41lhVlbnK-0F-9LcZjUu8clyw',
            '9469af87-3455-4853-a156-dbbc24965a26'
        );

        $this->assertEquals(403, $response->getStatusCode());
    }
}
