<?php

declare(strict_types=1);

namespace Tests\Domain\Contacts\Repositories;

use App\Domain\Contacts\Models\ContactKeyVisibilityModel;
use App\Domain\Contacts\Repositories\EloquentContactKeyVisibilityRepository;
use App\Domain\Integrations\KeyVisibility;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\TestCase;
use Tests\UuidTestFactory;

final class EloquentContactKeyVisibilityRepositoryTest extends TestCase
{
    private const ID = 'a40a0c9d-988e-48da-8543-fc74510ea495';

    private EloquentContactKeyVisibilityRepository $repository;

    protected function setUp(): void
    {
        Uuid::setFactory(new UuidTestFactory(['uuid4' => [self::ID]]));

        $this->repository = new EloquentContactKeyVisibilityRepository();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        Uuid::setFactory(new UuidFactory());

        parent::tearDown();
    }

    public function test_it_can_save_a_key_visibility(): void
    {
        $this->repository->save('info@publiq.be', KeyVisibility::v1);

        $this->assertDatabaseHas('contacts_key_visibility', [
            'id' => self::ID,
            'email' => 'info@publiq.be',
            'key_visibility' => KeyVisibility::v1,
        ]);
    }

    public function test_it_can_find_a_key_visibility_by_email(): void
    {
        ContactKeyVisibilityModel::query()->create([
            'id' => self::ID,
            'email' => 'info@publiq.be',
            'key_visibility' => KeyVisibility::all,
        ]);

        $keyVisibility = $this->repository->findByEmail('info@publiq.be');

        $this->assertEquals(KeyVisibility::all, $keyVisibility);
    }
}
