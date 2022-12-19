<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Controllers;

use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class SubscriptionController extends Controller
{
    private EloquentSubscriptionRepository $subscriptionRepository;

    public function __construct(EloquentSubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function index(): Response
    {
        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $this->subscriptionRepository->all(),
        ]);
    }
}
