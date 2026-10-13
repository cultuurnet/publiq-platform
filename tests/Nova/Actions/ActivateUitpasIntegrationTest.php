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
use App\Domain\UdbUuid;
use App\Nova\Actions\ActivateUitpasIntegration;
use App\Nova\Resources\Organization as OrganizationResource;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Exists;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
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

    public function test_the_organization_field_excludes_soft_deleted_organizations(): void
    {
        $fields = $this->handler->fields(NovaRequest::create('/'));

        $organizationField = $fields[0];

        $this->assertInstanceOf(BelongsTo::class, $organizationField);
        $this->assertFalse($organizationField->displaysWithTrashed);

        $rules = $organizationField->rules;

        $this->assertIsArray($rules);
        $this->assertSame('required', $rules[0]);
        $this->assertInstanceOf(Exists::class, $rules[1]);
        $this->assertSame('exists:organizations,id,deleted_at,"NULL"', (string) $rules[1]);
    }

    public function test_the_organizers_field_accepts_a_comma_separated_list_of_valid_ids(): void
    {
        $this->assertSame(
            [],
            $this->validateOrganizers(' d541dbd6-b818-432d-b2be-d51dfc5c0c51 , 68498691-4ff0-8010-ae61-c1ece25eaf38 ,')
        );
    }

    public function test_the_organizers_field_accepts_an_empty_value(): void
    {
        $this->assertSame([], $this->validateOrganizers(null));
        $this->assertSame([], $this->validateOrganizers(''));
    }

    public function test_the_organizers_field_rejects_ids_that_are_not_uuids(): void
    {
        $this->assertSame(
            [
                '"not-a-uuid" is not a valid organizer id.',
                '"https://udb.be/organizer/d541dbd6-b818-432d-b2be-d51dfc5c0c51" is not a valid organizer id.',
            ],
            $this->validateOrganizers(
                'not-a-uuid,d541dbd6-b818-432d-b2be-d51dfc5c0c51,https://udb.be/organizer/d541dbd6-b818-432d-b2be-d51dfc5c0c51'
            )
        );
    }

    /**
     * @return string[]
     */
    private function validateOrganizers(?string $organizers): array
    {
        $fields = $this->handler->fields(NovaRequest::create('/'));

        $organizersField = $fields[1];

        $this->assertInstanceOf(Text::class, $organizersField);
        $this->assertSame('organizers', $organizersField->attribute);

        $rules = $organizersField->rules;

        $this->assertIsArray($rules);
        $this->assertSame('nullable', $rules[0]);
        $this->assertSame('string', $rules[1]);
        $this->assertInstanceOf(Closure::class, $rules[2]);

        $failures = [];

        $rules[2]('organizers', $organizers, static function (string $message) use (&$failures): void {
            $failures[] = $message;
        });

        return $failures;
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
            ->with($this->callback(fn (UuidInterface $id): bool => $id->equals($integrationId)))
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn (UuidInterface $id): bool => $id->equals($integrationId)),
                $this->callback(fn (UuidInterface $id): bool => $id->equals($organizationId)),
                null,
                $this->callback(function (UdbOrganizers $organizers) use ($integrationId, $organizerId, $productionClient): bool {
                    /** @var UdbOrganizer $organizer */
                    $organizer = $organizers->first();

                    return $organizers->count() === 1
                        && $organizer->integrationId->equals($integrationId)
                        && $organizer->organizerId->toString() === $organizerId
                        && $organizer->status === UdbOrganizerStatus::Pending
                        && $organizer->clientId !== null
                        && $organizer->clientId->equals($productionClient->id);
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
                $this->callback(function (UdbOrganizers $organizers) use ($organizerIdA, $organizerIdB): bool {
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

    public function test_it_ignores_duplicate_ids_in_the_organizers_list(): void
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
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->anything(),
                $this->anything(),
                null,
                $this->callback(function (UdbOrganizers $organizers) use ($organizerId): bool {
                    $ids = array_map(
                        fn (UdbOrganizer $organizer): string => $organizer->organizerId->toString(),
                        $organizers->all()
                    );

                    return $ids === [$organizerId];
                })
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'organizers' => "{$organizerId},{$organizerId}",
            ]),
            collect()
        );

        $this->handler->handle($fields, new Collection([$integration]));
    }

    public function test_it_skips_organizers_that_are_already_attached_to_the_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $organizationId = Uuid::uuid4();
        $attachedOrganizerId = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';
        $newOrganizerId = '68498691-4ff0-8010-ae61-c1ece25eaf38';

        $integration = new IntegrationModel();
        $integration->id = $integrationId->toString();
        $integration->name = 'My UiTPAS integration';

        $organization = new OrganizationModel();
        $organization->id = $organizationId->toString();

        $domainIntegration = $this->givenThereIsAnIntegration($integrationId);
        $domainIntegration = $domainIntegration->withKeycloakClients($this->givenThereIsAKeycloakClient($domainIntegration));
        $domainIntegration = $domainIntegration->withUdbOrganizers(
            new UdbOrganizer(
                Uuid::uuid4(),
                $integrationId,
                new UdbUuid($attachedOrganizerId),
                UdbOrganizerStatus::Approved,
                null
            )
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->willReturn($domainIntegration);

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->anything(),
                $this->anything(),
                null,
                $this->callback(function (UdbOrganizers $organizers) use ($newOrganizerId): bool {
                    $ids = array_map(
                        fn (UdbOrganizer $organizer): string => $organizer->organizerId->toString(),
                        $organizers->all()
                    );

                    return $ids === [$newOrganizerId];
                })
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'organizers' => "{$attachedOrganizerId},{$newOrganizerId}",
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
                $this->callback(fn (UuidInterface $id): bool => $id->equals($integrationId)),
                $this->callback(fn (UuidInterface $id): bool => $id->equals($organizationId)),
                null,
                $this->callback(fn (UdbOrganizers $organizers): bool => count($organizers) === 0)
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
