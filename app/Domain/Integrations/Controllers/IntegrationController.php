<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\Router\TranslatedRoute;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Ramsey\Uuid\Uuid;

final class IntegrationController extends Controller
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly UiTiDv1ConsumerRepository $uitidV1ConsumerRepository,
        private readonly CurrentUser $currentUser
    ) {
    }

    public function index(Request $request): Response
    {
        $search = $request->query('search') ?? '';

        $integrationsData = $this->integrationRepository->getByContactEmail(
            $this->currentUser->email(),
            is_array($search) ? $search[0] : $search
        );

        $integrationIds = array_map(fn ($integration) => $integration->id, $integrationsData->collection->toArray());

        $auth0Clients = $this->auth0ClientRepository->getByIntegrationIds($integrationIds);
        $uitidV1Consumers = $this->uitidV1ConsumerRepository->getByIntegrationIds($integrationIds);

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrationsData->collection,
            'credentials' => [
                'auth0' => $auth0Clients,
                'uitidV1' => $uitidV1Consumers,
            ],
            'paginationInfo' => $integrationsData->paginationInfo,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Integrations/New', [
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
            $storeIntegration->input('emailFunctionalContact'),
            ContactType::Functional,
            $storeIntegration->input('firstNameFunctionalContact'),
            $storeIntegration->input('lastNameFunctionalContact')
        );

        $contactPartner = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $storeIntegration->input('emailTechnicalContact'),
            ContactType::Technical,
            $storeIntegration->input('firstNameTechnicalContact'),
            $storeIntegration->input('lastNameTechnicalContact')
        );

        $contributor = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $this->currentUser->email(),
            ContactType::Contributor,
            $this->currentUser->firstName(),
            $this->currentUser->lastName()
        );

        $integration = (
            new Integration(
                $integrationId,
                IntegrationType::from($storeIntegration->input('integrationType')),
                $storeIntegration->input('integrationName'),
                $storeIntegration->input('description'),
                Uuid::fromString($storeIntegration->input('subscriptionId')),
                IntegrationStatus::Draft
            )
        )->withContacts($contactOrganization, $contactPartner, $contributor);

        $this->integrationRepository->save($integration);

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName(
                request: $storeIntegration,
                routeName: 'integrations.index'
            )
        );
    }

    public function delete(Request $request, string $id): RedirectResponse
    {
        try {
            $this->integrationRepository->deleteById(Uuid::fromString($id));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
        }

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName(
                request: $request,
                routeName: 'integrations.index'
            )
        );

    }

    public function detail(string $id): Response
    {
        try {
            $integration = $this->integrationRepository->getById(Uuid::fromString($id));
        } catch (\Throwable $th) {
            abort(404);
        }

        return Inertia::render('Integrations/Detail', [
            'integration' => [
                ...$integration->toArray(),
                'contacts' => $integration->contacts(),
                'urls' => $integration->urls(),
            ],
        ]);
    }

}
