<?php

declare(strict_types=1);

namespace Tests\Domain\Histories\Repositories;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Histories\History;
use App\Domain\Histories\Repositories\EloquentHistoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentHistoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentHistoryRepository $historyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyRepository = new EloquentHistoryRepository();
    }

    public function test_it_can_save_a_history(): void
    {
        $history = new History(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'auth|0' . Uuid::uuid4()->toString(),
            'Contacts',
            ContactCreated::class,
            Carbon::now()
        );

        $this->historyRepository->create($history);

        $this->assertDatabaseHas('histories', [
            'id' => $history->id,
            'item_id' => $history->itemId,
            'user_id' => $history->userId,
            'type' => $history->type,
            'action' => $history->action,
            'timestamp' => $history->timestamp
        ]);
    }
}
