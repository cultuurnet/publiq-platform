<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Search\SearchService;
use CultuurNet\SearchV3\ValueObjects\Organizer;
use Illuminate\Console\Command;

final class SearchUiTPASOrganizer extends Command
{
    protected $signature = 'search:uitpas-organizer {name}';

    protected $description = 'Search UiTPAS organizer by name.';

    public function __construct(private readonly SearchService $searchService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        $this->info('Searching UiTPAS organizer by name: ' . $name);

        $organizers = $this->searchService->searchUiTPASOrganizer($name);

        if ($organizers->getMember() === null) {
            $this->info('No organizer found.');
            return 0;
        }

        /** @var Organizer $organizer */
        foreach ($organizers->getMember()->getItems() as $organizer) {
            if ($organizer->getName() === null) {
                $this->info('No name found for organizer.');
                continue;
            }

            $values = $organizer->getName()->getValues();
            $this->info('Organizer found: ' . current($values));
        }

        return 0;
    }
}
