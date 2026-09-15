<?php

declare(strict_types=1);

namespace Tests\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Nova\Actions\ActivateUitpasIntegration;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\CreatesTestData;
use Tests\TestCase;

final class ActivateUitpasIntegrationTest extends TestCase
{
    use CreatesTestData;

    private IntegrationRepository&MockObject $integrationRepository;
    private ActivateUitpasIntegration $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->handler = new ActivateUitpasIntegration($this->integrationRepository);
    }

    public function test_it_activates_the_integration_with_the_selected_organization_and_organizers(): void
    {
        $integrationId = Uuid::uuid4();
        $organizationId = Uuid::uuid4();
        $organizerId = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';

        $integration = new IntegrationModel();
        $integration->id = $integrationId->toString();
        $integration->name = 'My UiTPAS integration';

        $organization = new OrganizationModel();
        $organization->id = $organizationId->toString();

        $domainIntegration = $this->givenThereIsAnIntegration($integrationId);
        $domainIntegration = $domainIntegration->withKeycloakClients($this->givenThereIsAKeycloakClient($domainIntegration));

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->callback(fn ($id) => $id->equals($integrationId)))
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn ($id) => $id->equals($integrationId)),
                $this->callback(fn ($id) => $id->equals($organizationId)),
                null,
                $this->callback(fn (UdbOrganizers $organizers) => count($organizers) === 1)
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'organizers' => $organizerId,
            ]),
            collect()
        );

        $response = $this->handler->handle($fields, new Collection([$integration]));

        $json = $response->jsonSerialize();

        $this->assertEquals('Integration "My UiTPAS integration" activated.', $json['message']);
    }

    public function test_it_activates_the_integration_when_no_organizers_are_given(): void
    {
        $integrationId = Uuid::uuid4();
        $organizationId = Uuid::uuid4();

        $integration = new IntegrationModel();
        $integration->id = $integrationId->toString();
        $integration->name = 'My UiTPAS integration';

        $organization = new OrganizationModel();
        $organization->id = $organizationId->toString();

        $domainIntegration = $this->givenThereIsAnIntegration($integrationId);
        $domainIntegration = $domainIntegration->withKeycloakClients($this->givenThereIsAKeycloakClient($domainIntegration));

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->callback(fn ($id) => $id->equals($integrationId)))
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn ($id) => $id->equals($integrationId)),
                $this->callback(fn ($id) => $id->equals($organizationId)),
                null,
                $this->callback(fn (UdbOrganizers $organizers) => count($organizers) === 0)
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'organizers' => null,
            ]),
            collect()
        );

        $response = $this->handler->handle($fields, new Collection([$integration]));

        $json = $response->jsonSerialize();

        $this->assertEquals('Integration "My UiTPAS integration" activated.', $json['message']);
    }
}
