<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\Controllers;

use App\Domain\Newsletter\FormRequests\SubscribeRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Mailjet\Client;
use Mailjet\Resources;

final class NewsletterController extends Controller
{
    public function subscribe(SubscribeRequest $request): RedirectResponse
    {
        $mailJetClient = new Client(
            config('newsletter.api_key'),
            config('newsletter.api_secret')
        );
        $mailjetResponse = $mailJetClient->post(
            Resources::$ContactslistManagecontact,
            [
                'id' => config('newsletter.id'),
                'body' => [
                    'Email' => $request->input('email'),
                    'Action' => 'addnoforce',
                ],
            ]
        );

        return $mailjetResponse->success() ? Redirect::back() : Redirect::back()->withErrors(['mailjet' => $mailjetResponse->getReasonPhrase() ?? '']);
    }
}
