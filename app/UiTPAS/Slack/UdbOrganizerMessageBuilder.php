<?php

declare(strict_types=1);

namespace App\UiTPAS\Slack;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\UdbOrganizer;

final readonly class UdbOrganizerMessageBuilder
{
    public function __construct(
        private string $appUrl,
        private string $clientPermissionsLink
    ) {
    }

    public function toMessage(UdbOrganizer $org, Integration $integration): string
    {
        $client = $integration->getKeycloakClientByEnv(Environment::Production);

        $message = '*:robot_face: :incoming_envelope: ' . $integration->name . ' - requested access to organisation ' . $org->organizerId . '*';
        $message .= PHP_EOL . PHP_EOL;
        $message .= PHP_EOL . '• *Status:* _' . $integration->status->value . '_';
        $message .= PHP_EOL . '• *Description:* _' . $integration->description . '_';
        $message .= PHP_EOL . '• *Website:* _' . ($integration->website() ? $integration->website()->value : 'N/A') . '_';
        $message .= PHP_EOL;
        $message .= PHP_EOL . '• *Open in publiq-platform:* ' . $this->appUrl . '/admin/resources/integrations/' . $integration->id->toString();
        //@todo Not sure if we want to localize this URL on the env?
        $message .= PHP_EOL . '• *Open in UDB:* https://www.uitdatabank.be/organizers/' . $org->organizerId . '/preview';
        $message .= PHP_EOL . '• *Open in UiTPAS:* ' . $this->clientPermissionsLink . $client->clientId;
        return $message;
    }
}
