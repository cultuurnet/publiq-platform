<?php

declare(strict_types=1);

namespace Tests\Domain\Histories\Listeners;

use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Histories\Listeners\CreateHistory;
use App\Domain\Histories\Repositories\HistoryRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use Illuminate\Support\Facades\Auth;
use Iterator;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class CreateHistoryTest extends TestCase
{
    private CreateHistory $createHistory;

    private HistoryRepository&MockObject $historyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyRepository = $this->createMock(HistoryRepository::class);

        $currentUser = new CurrentUser(new Auth());
        $mockUser = Mockery::mock($currentUser);
        $mockUser->shouldReceive('id')
            ->andReturn(Uuid::uuid4());

        $this->createHistory = new CreateHistory(
            $this->historyRepository,
            $currentUser
        );
    }

    /**
     * @dataProvider provideEvents
     */
    public function test_something(string $eventName, array $data): void
    {
        $this->historyRepository->expects($this->once())
            ->method('create');

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
