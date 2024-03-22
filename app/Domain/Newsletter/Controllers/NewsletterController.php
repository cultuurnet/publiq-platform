<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\Controllers;

use App\Http\Controllers\Controller;
use Mailjet\Client;
use Mailjet\Resources;
use Mailjet\Response;

final class NewsletterController extends Controller
{
    public function handle(string $email): Response
    {
        $mailJetClient = new Client(
            config('newsletter.apiKey'),
            config('newsletter.apiSecret')
        );
        return $mailJetClient->post(
            Resources::$ContactslistManagecontact,
            [
                'id' => config('newsletter.id'),
                'body' => [
                    'Email' => $email,
                    'Action' => 'addnoforce',
                ],
            ]
        );
    }
}
