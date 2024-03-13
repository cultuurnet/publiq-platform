<?php

declare(strict_types=1);

namespace App\Nova\Actions\UiTiDv1;

use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

final class DistributeUiTiDv1Consumer extends Action
{
    public function __construct(private readonly UiTiDv1ConsumerRepository $repository)
    {
    }

    public function handle(ActionFields $fields, Collection $uiTiDv1Consumers): Action|ActionResponse
    {
        $this->repository->distribute(
            ...$uiTiDv1Consumers->map(fn (UiTiDv1ConsumerModel $uiTiDv1Consumer) => $uiTiDv1Consumer->toDomain())
        );

        return Action::message('Distributed UiTiDv1 consumers');
    }
}
