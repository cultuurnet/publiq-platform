<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Models;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/*
 * A queue job, artisan command or webhook writes models without an authenticated user. Spatie
 * then omits causer_id from the insert, which MySQL rejects in strict mode if the column is
 * NOT NULL. See 2026_10_01_090000_make_activity_log_causer_id_nullable.
 * */
final class ActivityLogCauserTest extends TestCase
{
    use RefreshDatabase;

    public function test_causer_id_is_nullable(): void
    {
        $column = DB::selectOne('SHOW COLUMNS FROM activity_log WHERE Field = ?', ['causer_id']);

        $this->assertSame('YES', $column->Null);
    }

    public function test_it_logs_activity_when_there_is_no_authenticated_causer(): void
    {
        $this->assertGuest();

        $integration = new IntegrationModel([
            'id' => Uuid::uuid4()->toString(),
            'type' => IntegrationType::EntryApi,
            'name' => 'Test Integration',
            'description' => 'Test Integration description',
            'subscription_id' => Uuid::uuid4()->toString(),
            'status' => IntegrationStatus::Draft,
        ]);
        $integration->save();

        activity()->performedOn($integration)->log('created');

        $activity = Activity::query()->where('subject_id', $integration->id)->first();

        $this->assertNotNull($activity);
        $this->assertNull($activity->causer_id);
    }
}
