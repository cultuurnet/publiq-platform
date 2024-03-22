<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Mailjet\Client;
use Mailjet\Resources;

final class NewsletterController extends Controller
{
    public function handle(string $email): JsonResponse
    {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $mailJetClient = new Client(
            config('newsletter.api_key'),
            config('newsletter.api_secret')
        );
        $mailjetResponse = $mailJetClient->post(
            Resources::$ContactslistManagecontact,
            [
                'id' => config('newsletter.id'),
                'body' => [
                    'Email' => $email,
                    'Action' => 'addnoforce',
                ],
            ]
        );
        return response()->json([
            'status' => $mailjetResponse->getReasonPhrase(),
        ], $mailjetResponse->getStatus() ?? 502);
    }
}
