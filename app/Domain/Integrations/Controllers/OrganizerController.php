<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Domain\Integrations\FormRequests\GetOrganizersRequest;
use App\Http\Controllers\Controller;
use App\Search\Sapi3\SearchService;
use CultuurNet\SearchV3\ValueObjects\Organizer;
use Illuminate\Http\RedirectResponse;

final class OrganizerController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function getOrganizers(GetOrganizersRequest $request): RedirectResponse
    {
        try {
            $organizerName = $request->input('name');

            $data = $this->searchService->searchUiTPASOrganizer($organizerName)->getMember()?->getItems() ?? [];

            $organizers = array_map(function (Organizer $organizer) {
                return [
                    'id' => $organizer->getCdbid(),
                    'name' => $organizer->getName()->getValues() ?? null,
                ];
            }, $data);

            return redirect()->back()->with('organizers', $organizers);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to fetch organizers: ' . $e->getMessage());
        }
    }
}
