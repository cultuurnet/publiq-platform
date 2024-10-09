<?php

declare(strict_types=1);

namespace App\Mails\MailJet;

use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailNotSend;
use Mailjet\Client;
use Mailjet\Resources;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;

final readonly class MailjetMailer implements Mailer
{
    public function __construct(
        private Client $client,
        private LoggerInterface $logger,
        private SandboxMode $sandboxMode,
    ) {

    }

    public function send(Address $from, Address $to, int $templateId, array $variables = []): void
    {
        $body = [
            'SandboxMode' => $this->sandboxMode->forAddress($to),
            'Messages' => [
                [
                    'From' => [
                        'Email' => $from->getAddress(),
                        'Name' => $from->getName(),
                    ],
                    'To' => [
                        [
                            'Email' => $to->getAddress(),
                            'Name' => $to->getName(),
                        ],
                    ],
                    'TemplateID' => $templateId,
                    'TemplateLanguage' => true,
                    'Variables' => $variables,
                ],
            ],
        ];

        $response = $this->client->post(
            Resources::$Email,
            ['body' => $body]
        );

        if (!$response->success()) {
            $this->logger->debug((string)json_encode($response->getData(), JSON_THROW_ON_ERROR));
            throw new MailNotSend($response->getReasonPhrase(), $variables);
        }

        $this->logger->info(
            sprintf('Mail "%s" sent successfully to %s', $templateId, $to->getAddress())
        );
    }
}
