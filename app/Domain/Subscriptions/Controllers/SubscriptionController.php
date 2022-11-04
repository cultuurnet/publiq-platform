<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Controllers;

use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class SubscriptionController extends Controller
{
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
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
