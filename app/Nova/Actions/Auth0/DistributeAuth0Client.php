<?php

declare(strict_types=1);

namespace App\Nova\Actions\Auth0;

use App\Auth0\Models\Auth0ClientModel;
use App\Auth0\Repositories\Auth0ClientRepository;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

final class DistributeAuth0Client extends Action
{
    public function __construct(private readonly Auth0ClientRepository $repository)
    {
    }

    public function handle(ActionFields $fields, Collection $auth0Clients): Action|ActionResponse
    {
        $this->repository->distribute(
            ...$auth0Clients->map(fn (Auth0ClientModel $auth0Client) => $auth0Client->toDomain())
        );

        return Action::message('Distributed Auth0 clients');
    }
}
