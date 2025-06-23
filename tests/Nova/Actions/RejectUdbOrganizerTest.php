<?php

declare(strict_types=1);

namespace Tests\Nova\Actions;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Nova\Actions\RejectUdbOrganizer;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class RejectUdbOrganizerTest extends TestCase
{
    use GivenUitpasOrganizers;

    public function test_it_handles_reject_udbOrganizer(): void
    {
        $udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);

        $uuid = Uuid::uuid4();
        $integrationUuid = Uuid::uuid4();

        $uuid2 = Uuid::uuid4();
        $integrationUuid2 = Uuid::uuid4();

        $expected = [
            new UdbOrganizer($uuid, $integrationUuid, 'org-1', UdbOrganizerStatus::Pending),
            new UdbOrganizer($uuid2, $integrationUuid2, 'org-2', UdbOrganizerStatus::Pending),
        ];

        $callIndex = 0;

        $udbOrganizerRepository->expects($this->exactly(2))
            ->method('delete')
            ->with($this->callback(function ($actual) use (&$callIndex, $expected) {
                TestCase::assertEquals($expected[$callIndex++], $actual);
                return true;
            }));

        $udbOrganizers = new Collection();
        $udbOrganizers->push($this->givenUdbOrganizerModel($uuid, $integrationUuid, 'org-1'));
        $udbOrganizers->push($this->givenUdbOrganizerModel($uuid2, $integrationUuid2, 'org-2'));

        (new RejectUdbOrganizer($udbOrganizerRepository))->handle(
            new ActionFields(collect(), collect()),
            $udbOrganizers
        );
    }

    private function givenUdbOrganizerModel(UuidInterface $uuid, UuidInterface $integrationUuid, string $orgId): UdbOrganizerModel
    {
        $model = new UdbOrganizerModel();
        $model->id = $uuid->toString();
        $model->integration_id = $integrationUuid->toString();
        $model->organizer_id = $orgId;
        $model->status = UdbOrganizerStatus::Pending->value;
        return $model;
    }
}
