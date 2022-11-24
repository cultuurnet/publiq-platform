<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Insightly\Exceptions\AuthenticationFailed;
use App\Insightly\Exceptions\BadRequest;
use App\Insightly\Exceptions\DeleteFailed;
use App\Insightly\Exceptions\RecordLimitReached;
use App\Insightly\Exceptions\RecordNotFound;
use App\Insightly\Resources\ContactResource;
use App\Insightly\Resources\OpportunityResource;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class InsightlyClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly string $apiKey,
        readonly Pipelines $pipelines
    ) {
    }

    public function contacts(): ContactResource
    {
        return new ContactResource($this);
    }

    public function opportunities(): OpportunityResource
    {
        return new OpportunityResource($this);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $requestWithHeaders = $request
            ->withAddedHeader(
                'Authorization',
                'Basic ' . base64_encode($this->apiKey . ':')
            )
            ->withAddedHeader(
                'Content-Type',
                'application/json'
            );

        $response = $this->httpClient->sendRequest($requestWithHeaders);
        $this->validateResponse($response);

        return $response;
    }

    private function validateResponse(ResponseInterface $response): void
    {
        switch ($response->getStatusCode()) {
            case 400:
                throw new BadRequest($response->getReasonPhrase());
            case 401:
                throw new AuthenticationFailed($response->getReasonPhrase());
            case 402:
                throw new RecordLimitReached($response->getReasonPhrase());
            case 404:
                throw new RecordNotFound($response->getReasonPhrase());
            case 417:
                throw new DeleteFailed($response->getReasonPhrase());
        }
    }
}
