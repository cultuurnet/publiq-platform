<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Slack;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\Website;
use App\Keycloak\Client;
use App\UiTPAS\Slack\UdbOrganizerMessageBuilder;
use Ramsey\Uuid\Uuid;
use Tests\CreateIntegration;
use Tests\TestCase;

final class UdbOrganizerMessageBuilderTest extends TestCase
{
    use CreateIntegration;

    public function test_it_builds_the_exact_expected_slack_message(): void
    {
        $organizer = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'org-1234'
        );

        $appUrl = 'https://platform.publiq.be';
        $clientPermissionsLink = 'https://uitid.test/admin/uitpas/clientpermissions/';

        $builder = new UdbOrganizerMessageBuilder($appUrl, $clientPermissionsLink);

        $integrationId = Uuid::uuid4();
        $integration = $this->givenThereIsAnIntegration($integrationId, ['status' => IntegrationStatus::Active])
            ->withWebsite(new Website('https://test.org'))
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-wrong', 'secret', Environment::Testing),
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-123', 'secret', Environment::Production),
            );

        $expectedMessage = '*:robot_face: :incoming_envelope: Mock Integration - requested access to organisation org-1234*' . PHP_EOL . PHP_EOL;
        $expectedMessage .= PHP_EOL . '• *Status:* _active_';
        $expectedMessage .= PHP_EOL . '• *Description:* _Mock description_';
        $expectedMessage .= PHP_EOL . '• *Website:* _https://test.org_';
        $expectedMessage .= PHP_EOL;
        $expectedMessage .= PHP_EOL . '• *Open in publiq-platform:* https://platform.publiq.be/admin/resources/integrations/' . $integrationId->toString();
        $expectedMessage .= PHP_EOL . '• *Open in UDB:* https://www.uitdatabank.be/organizers/org-1234/preview';
        $expectedMessage .= PHP_EOL . '• *Open in UiTPAS:* https://uitid.test/admin/uitpas/clientpermissions/client-123';

        $this->assertSame($expectedMessage, $builder->toMessage($organizer, $integration));
    }
}
