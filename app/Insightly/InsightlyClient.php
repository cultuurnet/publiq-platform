<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Insightly\Resources\ContactResource;
use App\Insightly\Resources\OpportunityResource;
use App\Insightly\Resources\OrganizationResource;
use App\Insightly\Resources\ProjectResource;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface InsightlyClient
{
    public function contacts(): ContactResource;

    public function opportunities(): OpportunityResource;

    public function projects(): ProjectResource;

    public function organizations(): OrganizationResource;

    public function sendRequest(RequestInterface $request): ResponseInterface;

    public function getPipelines(): Pipelines;
}
