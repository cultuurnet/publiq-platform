<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Nova\Actions\UdbOrganizer\RejectUdbOrganizer;
use App\UiTPAS\Event\UdbOrganizerRejected;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
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
            ['integration_id' => $integrationUuid, 'organizer_id' => new UdbUuid('d541dbd6-b818-432d-b2be-d51dfc5c0c51')],
            ['integration_id' => $integrationUuid2, 'organizer_id' => new UdbUuid('33f1722b-04fc-4652-b99f-2c96de87cf82')],
        ];

        $callIndex = 0;

        $udbOrganizerRepository->expects($this->exactly(2))
            ->method('delete')
            ->with($this->callback(function ($actual) use (&$callIndex, $expected) {
                TestCase::assertEquals($expected[$callIndex]['integration_id'], $actual);
                return true;
            }), $this->callback(function ($actual) use (&$callIndex, $expected) {
                TestCase::assertEquals($expected[$callIndex++]['organizer_id'], $actual);
                return true;
            }));

        $udbOrganizers = new Collection();
        $udbOrganizers->push($this->givenUdbOrganizerModel($uuid, $integrationUuid, 'd541dbd6-b818-432d-b2be-d51dfc5c0c51'));
        $udbOrganizers->push($this->givenUdbOrganizerModel($uuid2, $integrationUuid2, '33f1722b-04fc-4652-b99f-2c96de87cf82'));

        (new RejectUdbOrganizer($udbOrganizerRepository))->handle(
            new ActionFields(collect(), collect()),
            $udbOrganizers
        );

        Event::assertDispatched(UdbOrganizerRejected::class);
    }

    private function givenUdbOrganizerModel(UuidInterface $uuid, UuidInterface $integrationUuid, string $orgId): UdbOrganizerModel
    {
        $model = new UdbOrganizerModel();
        $model->id = $uuid->toString();
        $model->integration_id = $integrationUuid->toString();
        $model->organizer_id = $orgId;
        $model->status = UdbOrganizerStatus::Pending->value;
        $model->client_id  = Uuid::uuid4()->toString();
        return $model;
    }
}
