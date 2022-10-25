<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class IntegrationController extends Controller
{
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function index(): Response
    {
        return Inertia::render('Integrations/Index', [
            'integrations' => $this->integrationRepository->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Integrations/Create', [
            'integrationTypes' => IntegrationType::cases(),
            'subscriptions' => $this->subscriptionRepository->all(),
        ]);
    }
}
