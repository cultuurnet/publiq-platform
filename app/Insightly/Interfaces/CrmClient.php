<?php

declare(strict_types=1);

namespace App\Insightly\Interfaces;

use App\Insightly\Pipelines;
use App\Insightly\Resources\InsightlyOrganizationResource;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface CrmClient
{
    public function contacts(): ContactResource;

    public function opportunities(): OpportunityResource;

    public function organizations(): InsightlyOrganizationResource;

    public function sendRequest(RequestInterface $request): ResponseInterface;

    public function getPipelines(): Pipelines;
}
