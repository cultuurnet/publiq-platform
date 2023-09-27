<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Auth\CurrentUser;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StoreIntegrationMapperTest extends TestCase
{
    public function test_it_creates_an_integration_from_request(): void
    {
        $request = new StoreIntegrationRequest();

        Auth::shouldReceive('user')
            ->once()
            ->andReturn(UserModel::createSystemUser());

        $currentUser = new CurrentUser(App::get(Auth::class));

        $integration = StoreIntegrationMapper::map($request, $currentUser);
    }
}
