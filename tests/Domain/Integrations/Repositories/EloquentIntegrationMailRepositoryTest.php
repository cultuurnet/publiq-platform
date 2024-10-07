<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationMail;
use App\Domain\Integrations\Repositories\EloquentIntegrationMailRepository;
use App\Mails\Template\TemplateName;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentIntegrationMailRepositoryTest extends TestCase
{
    public function test_it_saves_record_in_db(): void
    {
        $repository = new EloquentIntegrationMailRepository();

        $id = Uuid::uuid4();
        $integrationId = Uuid::uuid4();
        $templateName = TemplateName::INTEGRATION_ACTIVATION_REMINDER;

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $repository->create(new IntegrationMail(
            $id,
            $integrationId,
            $templateName,
        ));

        $this->assertDatabaseHas('integrations_mails', [
            'uuid' => $id,
            'integration_id' => $integrationId,
            'template_name' => $templateName,
            'created_at' => $now,
        ]);
    }
}
