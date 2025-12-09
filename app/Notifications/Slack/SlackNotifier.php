<?php

declare(strict_types=1);

namespace App\Notifications\Slack;

use App\Notifications\Notifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

final readonly class SlackNotifier implements Notifier
{
    public function __construct(
        private string $botToken,
        private string $channelId,
        private string $baseUri
    ) {
    }

    public function postMessage(string $message): void
    {
        $response = Http::withToken($this->botToken)
            ->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
            ])
            ->post(
                $this->baseUri . 'chat.postMessage',
                [
                    'channel' => $this->channelId,
                    'text' => $message,
                ]
            );

        assert($response instanceof Response);

        $this->handleResponse($response, 'Failed to post message.');
    }

    private function handleResponse(Response $response, string $message): void
    {
        Log::info($response->body());

        $contents = $response->body();

        if ($response->status() !== 200) {
            throw new \RuntimeException($message . ' Response: ' . $contents);
        }

        $json = json_decode($contents, true);
        if ($json['ok'] !== true) {
            throw new \RuntimeException($message . ' Response: ' . $contents);
        }
    }
}
