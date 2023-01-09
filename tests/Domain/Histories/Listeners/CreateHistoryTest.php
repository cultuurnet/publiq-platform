<?php

declare(strict_types=1);

namespace Tests\Domain\Histories\Listeners;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Histories\EventToModelMapping;
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
use Ramsey\Uuid\Uuid;
use Tests\MockUser;
use Tests\TestCase;

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

        $this->createHistory = new CreateHistory(
            $this->historyRepository,
            $currentUser
        );
    }

    /**
     * @dataProvider provideEvents
     */
    public function test_saving_history(string $eventName, array $data): void
    {
        $this->historyRepository->expects($this->once())
            ->method('create')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf(History::class),
                    $this->containsEqual($data[0]->id),
                    $this->containsEqual($this->userId),
                    $this->containsEqual(EventToModelMapping::MAPPING[$eventName]),
                    $this->containsEqual($eventName),
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
        ];

        yield 'integration created' => [
            'eventName' => IntegrationCreated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
        ];

        yield 'organization created' => [
            'eventName' => OrganizationCreated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
        ];

        yield 'organization deleted' => [
            'eventName' => OrganizationDeleted::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
        ];

        yield 'organization updated' => [
            'eventName' => OrganizationUpdated::class,
            'data' => [
                0 => (object)[
                    'id' => Uuid::uuid4(),
                ],
            ],
        ];
    }
}
