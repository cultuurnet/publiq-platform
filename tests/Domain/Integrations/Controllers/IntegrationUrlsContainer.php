<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Controllers;

use App\Domain\Integrations\IntegrationUrl;

final readonly class IntegrationUrlsContainer
{
    /**
     * @param array<IntegrationUrl> $callbackUrls
     * @param array<IntegrationUrl> $logoutUrls
     */
    public function __construct(
        public IntegrationUrl $loginUrl,
        public array          $callbackUrls,
        public array          $logoutUrls,
    ) {
    }
}
