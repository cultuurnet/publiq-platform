<?php

declare(strict_types=1);

namespace App\ProjectAanvraag\Listeners;

use App\Auth0\Repositories\Auth0UserRepository;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\ProjectAanvraag\ProjectAanvraagClient;
use App\ProjectAanvraag\Requests\CreateWidgetRequest;
use App\UiTiDv1\Events\ConsumerCreated;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

final class CreateWidget implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProjectAanvraagClient $projectAanvraagClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly UiTiDv1ConsumerRepository $uiTiDv1ConsumerRepository,
        private readonly int $groupId,
        private readonly Auth0UserRepository $auth0UserRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handleIntegrationCreated(IntegrationCreated $integrationCreated): void
    {
        $this->handle($integrationCreated->integrationId);
    }

    public function handleContactCreated(ContactCreated $contactCreated): void
    {
        $contact = $this->contactRepository->getById($contactCreated->id);
        $this->handle($contact->integrationId);
    }

    public function handleConsumerCreated(ConsumerCreated $consumerCreated): void
    {
        $consumer = $this->uiTiDv1ConsumerRepository->getById($consumerCreated->id);
        $this->handle($consumer->integrationId);
    }

    private function handle(UuidInterface $integrationId): void
    {
        $integration = $this->integrationRepository->getById($integrationId);
        if ($integration->type !== IntegrationType::Widgets) {
            $this->logger->info(
                'Integration {integrationId} is not a widget integration, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }

        $contacts = $this->contactRepository->getByIntegrationId($integration->id);
        if ($contacts->count() === 0) {
            $this->logger->info(
                'Integration {integrationId} has no contacts, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }
        $contributor = $contacts->firstWhere('type', ContactType::Contributor);
        if ($contributor === null) {
            $this->logger->info(
                'Integration {integrationId} has no contributor, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }

        $userId = $this->auth0UserRepository->findUserIdByEmail($contributor->email);
        if ($userId === null) {
            $this->logger->info(
                'Integration {integrationId} Auth0 contact {$email} not found, skipping widget creation',
                [
                    'integrationId' => $integration->id->toString(),
                    'email' => $contributor->email,
                ]
            );
            return;
        }

        $uiTiDv1Consumers = $this->uiTiDv1ConsumerRepository->getByIntegrationId($integration->id);
        if (count($uiTiDv1Consumers) === 0) {
            $this->logger->info(
                'Integration {integrationId} has no UiTiDv1 consumers, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }
        $testKey = null;
        $liveKey = null;
        foreach ($uiTiDv1Consumers as $uiTiDv1Consumer) {
            if ($uiTiDv1Consumer->environment === UiTiDv1Environment::Testing) {
                $testKey = $uiTiDv1Consumer->apiKey;
            }
            if ($uiTiDv1Consumer->environment === UiTiDv1Environment::Production) {
                $liveKey = $uiTiDv1Consumer->apiKey;
            }
        }
        if ($testKey === null) {
            $this->logger->info(
                'Integration {integrationId} has no UiTiDv1 testing consumer, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }
        if ($liveKey === null) {
            $this->logger->info(
                'Integration {integrationId} has no UiTiDv1 production consumer, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }

        $this->projectAanvraagClient->createWidget(
            new CreateWidgetRequest(
                $integration->id,
                $userId,
                $integration->name,
                $integration->description,
                $integration->status,
                $this->groupId,
                $testKey,
                $liveKey
            )
        );
    }
}
