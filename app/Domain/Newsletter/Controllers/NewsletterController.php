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
        $mailingListId = config('newsletter.id');
        $apiKey = config('newsletter.apiKey');
        $apiSecret = config('newsletter.apiSecret');

        $mailJetClient = new Client($apiKey, $apiSecret);
        return $mailJetClient->post(
            Resources::$ContactslistManagecontact,
            [
                'id' => $mailingListId,
                'body' => [
                    'Email' => $email,
                    'Action' => 'addnoforce',
                ],
            ]
        );
    }
}
