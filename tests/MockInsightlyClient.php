<?php

declare(strict_types=1);

namespace Tests;

use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use App\Insightly\Resources\ContactResource;
use App\Insightly\Resources\OpportunityResource;
use App\Insightly\Resources\OrganizationResource;
use App\Insightly\Resources\ProjectResource;
use PHPUnit\Framework\MockObject\MockObject;

trait MockInsightlyClient
{
    private InsightlyClient&MockObject $insightlyClient;

    private ContactResource&MockObject $contactResource;

    private OpportunityResource&MockObject $opportunityResource;

    private ProjectResource&MockObject $projectResource;

    private OrganizationResource&MockObject $organizationResource;

    private function mockCrmClient(?Pipelines $pipelines = null): void
    {
        $this->insightlyClient = $this->createMock(InsightlyClient::class);
        $this->contactResource = $this->createMock(ContactResource::class);
        $this->opportunityResource = $this->createMock(OpportunityResource::class);
        $this->projectResource = $this->createMock(ProjectResource::class);
        $this->organizationResource = $this->createMock(OrganizationResource::class);

        $this->insightlyClient
            ->method('contacts')
            ->willReturn($this->contactResource);
        $this->insightlyClient
            ->method('opportunities')
            ->willReturn($this->opportunityResource);
        $this->insightlyClient
            ->method('projects')
            ->willReturn($this->projectResource);
        $this->insightlyClient
            ->method('organizations')
            ->willReturn($this->organizationResource);

        if ($pipelines) {
            $this->insightlyClient
                ->method('getPipelines')
                ->willReturn($pipelines);
        }
    }
}
