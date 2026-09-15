<?php

declare(strict_types=1);

namespace Tests\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Nova\Actions\ActivateIntegration;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
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
                $this->callback(fn ($id) => $id->equals($integrationId)),
                $this->callback(fn ($id) => $id->equals($organizationId)),
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

        $this->assertEquals('Integration "My integration" activated.', $json['message']);
    }
}
