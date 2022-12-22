<?php

declare(strict_types=1);

namespace Tests;

use App\Insightly\CrmClient;
use App\Insightly\Pipelines;
use App\Insightly\Resources\ContactResource;
use App\Insightly\Resources\OpportunityResource;
use App\Insightly\Resources\OrganizationResource;
use PHPUnit\Framework\MockObject\MockObject;

trait MockCrmClient
{
    private CrmClient&MockObject $insightlyClient;

    private ContactResource&MockObject $contactResource;

    private OpportunityResource&MockObject $opportunityResource;

    private OrganizationResource&MockObject $organizationResource;

    private function mockCrmClient(?Pipelines $pipelines = null): void
    {
        $this->insightlyClient = $this->createMock(CrmClient::class);
        $this->contactResource = $this->createMock(ContactResource::class);
        $this->opportunityResource = $this->createMock(OpportunityResource::class);
        $this->organizationResource = $this->createMock(OrganizationResource::class);

        $this->insightlyClient->expects($this->any())
            ->method('contacts')
            ->willReturn($this->contactResource);
        $this->insightlyClient->expects($this->any())
            ->method('opportunities')
            ->willReturn($this->opportunityResource);
        $this->insightlyClient->expects($this->any())
            ->method('organizations')
            ->willReturn($this->organizationResource);

        if ($pipelines) {
            $this->insightlyClient->expects($this->any())
                ->method('getPipelines')
                ->willReturn($pipelines);
        }
    }
}
