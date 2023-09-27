<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Auth\CurrentUser;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreIntegrationMapperTest extends TestCase
{

    public function test_it_creates_an_integration_from_request() {
        $request = new StoreIntegrationRequest();

        // This causes problem
        $auth = $this->createMock(Auth::class);

        // mocking only currentUser isn't possible since it is a final class

        // I need to mock currentUser
        $currentUser = new CurrentUser($auth);

        $integration = StoreIntegrationMapper::map($request, $currentUser);
    }
}
