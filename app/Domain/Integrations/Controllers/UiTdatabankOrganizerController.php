<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Domain\Integrations\FormRequests\GetUiTdatabankOrganizersRequest;
use App\Http\Controllers\Controller;
use App\Search\Sapi3\SearchService;
use CultuurNet\SearchV3\ValueObjects\Organizer;
use Illuminate\Http\JsonResponse;

final class UiTdatabankOrganizerController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function index(GetUiTdatabankOrganizersRequest $request): JsonResponse
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
                ['exception' => $e->getMessage()]
            );
            ;
        }
    }
}
