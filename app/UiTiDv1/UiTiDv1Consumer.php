<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use JsonSerializable;
use Ramsey\Uuid\UuidInterface;

final readonly class UiTiDv1Consumer implements JsonSerializable
{
    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public string $consumerId,
        public string $consumerKey,
        public string $consumerSecret,
        public string $apiKey,
        public UiTiDv1Environment $environment
    ) {
    }

    /**
     * Only the api key is shown to integrators. consumerId, consumerKey and consumerSecret are
     * deliberately left out so they never reach the browser through an Inertia payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'integrationId' => $this->integrationId,
            'apiKey' => $this->apiKey,
            'environment' => $this->environment,
        ];
    }
}
