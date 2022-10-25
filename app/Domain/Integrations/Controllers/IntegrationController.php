<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Ramsey\Uuid\Uuid;

final class IntegrationController extends Controller
{
    private SubscriptionRepository $subscriptionRepository;
    private IntegrationRepository $integrationRepository;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        IntegrationRepository $integrationRepository
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->integrationRepository = $integrationRepository;
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

    public function store(StoreIntegration $storeIntegration): RedirectResponse
    {
        $integrationId = Uuid::uuid4();

        $contactOrganization = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Organization,
            $storeIntegration->input('firstNameOrganisation'),
            $storeIntegration->input('lastNameOrganisation'),
            $storeIntegration->input('emailOrganisation')
        );

        $contactPartner = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Technical,
            $storeIntegration->input('firstNamePartner'),
            $storeIntegration->input('lastNamePartner'),
            $storeIntegration->input('emailPartner')
        );

        $integration = new Integration(
            $integrationId,
            IntegrationType::from($storeIntegration->input('integrationType')),
            $storeIntegration->input('name'),
            $storeIntegration->input('description'),
            Uuid::fromString($storeIntegration->input('subscriptionId')),
            [
                $contactOrganization, $contactPartner
            ]
        );

        $this->integrationRepository->save($integration);

        return Redirect::route('integrations.index');
    }
}
