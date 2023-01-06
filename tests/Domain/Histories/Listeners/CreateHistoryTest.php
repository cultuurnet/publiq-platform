<?php

declare(strict_types=1);

namespace Tests\Domain\Histories\Listeners;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Histories\History;
use App\Domain\Histories\Listeners\CreateHistory;
use App\Domain\Histories\Repositories\HistoryRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use Illuminate\Support\Carbon;
use Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MockUser;

final class CreateHistoryTest extends TestCase
{
    use MockUser;

    private CreateHistory $createHistory;

    private HistoryRepository&MockObject $historyRepository;

    private string $userId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2023-01-06');

        $this->historyRepository = $this->createMock(HistoryRepository::class);

        $currentUser = $this->createMockUser();
        $this->userId = $currentUser->id();

        $this->createHistory = new CreateHistory(
            $this->historyRepository,
            $currentUser
        );
    }

    /**
     * @dataProvider provideEvents
     */
    public function test_saving_history(string $eventName, array $data, string $type, string $action): void
    {
        $this->historyRepository->expects($this->once())
            ->method('create')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf(History::class),
                    $this->containsEqual($data[0]->id),
                    $this->containsEqual($this->userId),
                    $this->containsEqual($type),
                    $this->containsEqual($action),
                    $this->containsEqual(Carbon::now())
                )
            );

        $this->createHistory->handle($eventName, $data);
    }

    public function provideEvents(): Iterator
    {
        yield 'create contact' => [
            'eventName' => ContactCreated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
            'type' => 'Contacts',
            'action' => 'ContactCreated',
        ];

        yield 'integration created' => [
            'eventName' => IntegrationCreated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
            'type' => 'Integrations',
            'action' => 'IntegrationCreated',
        ];

        yield 'organization created' => [
            'eventName' => OrganizationCreated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
            'type' => 'Organizations',
            'action' => 'OrganizationCreated',
        ];

        yield 'organization deleted' => [
            'eventName' => OrganizationDeleted::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
            'type' => 'Organizations',
            'action' => 'OrganizationDeleted',
        ];

        yield 'organization updated' => [
            'eventName' => OrganizationUpdated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
            'type' => 'Organizations',
            'action' => 'OrganizationUpdated',
        ];
    }
}
