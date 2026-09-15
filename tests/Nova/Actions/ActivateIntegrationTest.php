<?php

declare(strict_types=1);

namespace Tests\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Nova\Actions\ActivateIntegration;
use App\Nova\Resources\Organization as OrganizationResource;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\TestCase;

final class ActivateIntegrationTest extends TestCase
{
    private IntegrationRepository&MockObject $integrationRepository;
    private ActivateIntegration $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->handler = new ActivateIntegration($this->integrationRepository);
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

    public function test_it_activates_the_integration_with_the_selected_organization(): void
    {
        $integrationId = Uuid::uuid4();
        $organizationId = Uuid::uuid4();

        $integration = new IntegrationModel();
        $integration->id = $integrationId->toString();
        $integration->name = 'My integration';

        $organization = new OrganizationModel();
        $organization->id = $organizationId->toString();

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn (UuidInterface $id) => $id->equals($integrationId)),
                $this->callback(fn (UuidInterface $id) => $id->equals($organizationId)),
                'COUPON123'
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'coupon' => 'COUPON123',
            ]),
            collect()
        );

        $response = $this->handler->handle($fields, new Collection([$integration]));

        $json = $response->jsonSerialize();

        $this->assertSame('Integration "My integration" activated.', (string) $json['message']);
    }

    public function test_it_activates_the_integration_without_a_coupon(): void
    {
        $integrationId = Uuid::uuid4();
        $organizationId = Uuid::uuid4();

        $integration = new IntegrationModel();
        $integration->id = $integrationId->toString();
        $integration->name = 'My integration';

        $organization = new OrganizationModel();
        $organization->id = $organizationId->toString();

        $this->integrationRepository->expects($this->once())
            ->method('activateWithOrganization')
            ->with(
                $this->callback(fn (UuidInterface $id) => $id->equals($integrationId)),
                $this->callback(fn (UuidInterface $id) => $id->equals($organizationId)),
                null
            );

        $fields = new ActionFields(
            collect([
                'organization' => $organization,
                'coupon' => null,
            ]),
            collect()
        );

        $response = $this->handler->handle($fields, new Collection([$integration]));

        $json = $response->jsonSerialize();

        $this->assertSame('Integration "My integration" activated.', (string) $json['message']);
    }
}
