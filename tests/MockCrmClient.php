<?php

declare(strict_types=1);

namespace Tests;

use App\Insightly\Interfaces\ContactResource;
use App\Insightly\Interfaces\CrmClient;
use App\Insightly\Interfaces\OpportunityResource;
use App\Insightly\Pipelines;
use PHPUnit\Framework\MockObject\MockObject;

trait MockCrmClient
{
    private CrmClient&MockObject $insightlyClient;

    private ContactResource&MockObject $contactResource;

    private OpportunityResource&MockObject $opportunityResource;

    private function mockCrmClient(?Pipelines $pipelines = null): void
    {
        $this->insightlyClient = $this->createMock(CrmClient::class);
        $this->contactResource = $this->createMock(ContactResource::class);
        $this->opportunityResource = $this->createMock(OpportunityResource::class);
        $this->insightlyClient->expects($this->any())
            ->method('contacts')
            ->willReturn($this->contactResource);
        $this->insightlyClient->expects($this->any())
            ->method('opportunities')
            ->willReturn($this->opportunityResource);

        if ($pipelines) {
            $this->insightlyClient->expects($this->any())
                ->method('getPipelines')
                ->willReturn($pipelines);
        }
    }
}
