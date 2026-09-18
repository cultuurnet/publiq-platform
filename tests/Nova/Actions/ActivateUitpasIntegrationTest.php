<?php

declare(strict_types=1);

namespace Tests\Nova\Actions;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Nova\Actions\ActivateUitpasIntegration;
use App\Nova\Resources\Organization as OrganizationResource;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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

    public function test_the_organization_field_is_a_searchable_belongs_to(): void
    {
        $fields = $this->handler->fields(NovaRequest::create('/'));

        $organizationField = $fields[0];

        $this->assertInstanceOf(BelongsTo::class, $organizationField);
        $this->assertSame('organization', $organizationField->attribute);
        $this->assertSame(OrganizationResource::class, $organizationField->resourceClass);
        $this->assertTrue($organizationField->searchable);
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
        $productionClient = $domainIntegration->getKeycloakClientByEnv(Environment::Production);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->callback(fn (UuidInterface $id) => $id->equals($integrationId)))
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn (UuidInterface $id) => $id->equals($integrationId)),
                $this->callback(fn (UuidInterface $id) => $id->equals($organizationId)),
                null,
                $this->callback(function (UdbOrganizers $organizers) use ($integrationId, $organizerId, $productionClient) {
                    /** @var UdbOrganizer $organizer */
                    $organizer = $organizers->first();

                    return $organizers->count() === 1
                        && $organizer->integrationId->equals($integrationId)
                        && $organizer->organizerId->toString() === $organizerId
                        && $organizer->status === UdbOrganizerStatus::Pending
                        && $organizer->clientId?->equals($productionClient->id);
                })
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

        $this->assertSame('Integration "My UiTPAS integration" activated.', (string) $json['message']);
    }

    public function test_it_trims_whitespace_and_ignores_trailing_commas_in_the_organizers_list(): void
    {
        $integrationId = Uuid::uuid4();
        $organizationId = Uuid::uuid4();
        $organizerIdA = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';
        $organizerIdB = '68498691-4ff0-8010-ae61-c1ece25eaf38';

        $integration = new IntegrationModel();
        $integration->id = $integrationId->toString();
        $integration->name = 'My UiTPAS integration';

        $organization = new OrganizationModel();
        $organization->id = $organizationId->toString();

        $domainIntegration = $this->givenThereIsAnIntegration($integrationId);
        $domainIntegration = $domainIntegration->withKeycloakClients($this->givenThereIsAKeycloakClient($domainIntegration));

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->anything(),
                $this->anything(),
                null,
                $this->callback(function (UdbOrganizers $organizers) use ($organizerIdA, $organizerIdB) {
                    $ids = array_map(
                        fn (UdbOrganizer $organizer): string => $organizer->organizerId->toString(),
                        $organizers->all()
                    );

                    return $ids === [$organizerIdA, $organizerIdB];
                })
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'organizers' => " {$organizerIdA} , {$organizerIdB} ,",
            ]),
            collect()
        );

        $this->handler->handle($fields, new Collection([$integration]));
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

        $this->integrationRepository->expects($this->never())
            ->method('getById');

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn (UuidInterface $id) => $id->equals($integrationId)),
                $this->callback(fn (UuidInterface $id) => $id->equals($organizationId)),
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

        $this->assertSame('Integration "My UiTPAS integration" activated.', (string) $json['message']);
    }
}
