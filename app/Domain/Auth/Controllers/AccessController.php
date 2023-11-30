<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Http\Controllers\Controller;
use Auth0\SDK\Contract\Auth0Interface;
use Auth0\SDK\Token;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;

final class AccessController extends Controller
{
    public function __construct(
        private readonly Auth0Interface $auth0,
        private readonly IntegrationRepository $integrationRepository,
        private readonly array $adminEmails,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(string $idToken, string $integrationId): JsonResponse
    {
        $this->logger->info('Requested IntegrationAccess for integration ' . $integrationId);

        try {
            $token = new Token($this->auth0->configuration(), $idToken, Token::TYPE_ID_TOKEN);
            $tokenAsArray = $token->toArray();
        } catch (\Throwable $exception) {
            $this->logger->warning('Invalid token', ['exception' => $exception->getMessage()]);
            return new JsonResponse(
                ['exception' => $exception->getMessage()],
                400
            );
        }

        if (!isset($tokenAsArray['email'])) {
            $this->logger->warning('No email in token');
            return new JsonResponse(
                ['exception' => 'No email in token'],
                400
            );
        }
        $email = $token->toArray()['email'];

        if (in_array($email, $this->adminEmails)) {
            $this->logger->info('Admin access for ' . $email);
            return new JsonResponse();
        }

        $integration = $this->integrationRepository->getById(Uuid::fromString($integrationId));
        $hasAccess = $integration->contactHasAccess($email);
        $this->logger->info(
            'IntegrationAccess for ',
            [
                'email' => $email,
                'integrationId' => $integrationId,
                'hasAccess' => $hasAccess,
            ]
        );

        return $hasAccess ? new JsonResponse() : new JsonResponse(null, 403);
    }
}
