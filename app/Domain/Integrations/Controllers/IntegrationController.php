<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\AuthController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;

final class IntegrationController extends AuthController
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly IntegrationRepository $integrationRepository,
        readonly Auth $auth
    ) {
        parent::__construct($auth);
    }

    public function index(): Response
    {
        return Inertia::render('Integrations/Index', [
            'integrations' => $this->integrationRepository->getByContactEmail(
                $this->getUser()->email
            ),
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
            $storeIntegration->input('emailOrganisation'),
            ContactType::Functional,
            $storeIntegration->input('firstNameOrganisation'),
            $storeIntegration->input('lastNameOrganisation')
        );

        $contactPartner = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $storeIntegration->input('emailPartner'),
            ContactType::Technical,
            $storeIntegration->input('firstNamePartner'),
            $storeIntegration->input('lastNamePartner')
        );

        $contributor = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $this->getUser()->email,
            ContactType::Contributor,
            $this->getUser()->name,
            ''
        );

        $integration = new Integration(
            $integrationId,
            IntegrationType::from($storeIntegration->input('integrationType')),
            $storeIntegration->input('name'),
            $storeIntegration->input('description'),
            Uuid::fromString($storeIntegration->input('subscriptionId')),
            [
                $contactOrganization, $contactPartner, $contributor,
            ]
        );

        $this->integrationRepository->save($integration);

        return Redirect::route('integrations.index');
    }
}
