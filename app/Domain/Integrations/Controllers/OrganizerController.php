<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Domain\Integrations\FormRequests\GetOrganizersRequest;
use App\Http\Controllers\Controller;
use App\Search\Sapi3\SearchService;
use CultuurNet\SearchV3\ValueObjects\Organizer;
use Illuminate\Http\JsonResponse;

final class OrganizerController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function index(GetOrganizersRequest $request): JsonResponse
    {
        try {
            $organizerName = $request->input('name');

            $data = $this->searchService->searchUiTPASOrganizer($organizerName)->getMember()?->getItems() ?? [];

            return new JsonResponse(
                array_map(function (Organizer $organizer) {
                    return [
                        'id' => $organizer->getCdbid(),
                        'name' => $organizer->getName()?->getValues(),
                    ];
                }, $data)
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['exception' => $e->getMessage()],
                500
            );
            ;
        }
    }
}
