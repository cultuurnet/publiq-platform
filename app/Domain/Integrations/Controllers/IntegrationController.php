<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactKeyVisibilityRepository;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationCreatedPost;
use App\Domain\Integrations\FormRequests\KeyVisibilityUpgradeRequest;
use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\StoreContactRequest;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\FormRequests\StoreIntegrationUrlRequest;
use App\Domain\Integrations\FormRequests\UpdateContactInfoRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUdbOrganizersRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlsRequest;
use App\Domain\Integrations\FormRequests\UpdateOrganizationRequest;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Mappers\KeyVisibilityUpgradeMapper;
use App\Domain\Integrations\Mappers\OrganizationMapper;
use App\Domain\Integrations\Mappers\UdbOrganizerMapper;
use App\Domain\Integrations\Mappers\StoreContactMapper;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use App\Domain\Integrations\Mappers\StoreIntegrationUrlMapper;
use App\Domain\Integrations\Mappers\UpdateContactInfoMapper;
use App\Domain\Integrations\Mappers\UpdateIntegrationMapper;
use App\Domain\Integrations\Mappers\UpdateIntegrationUrlsMapper;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\IntegrationUrlRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use App\Domain\KeyVisibilityUpgrades\Repositories\KeyVisibilityUpgradeRepository;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\ProjectAanvraag\ProjectAanvraagUrl;
use App\Router\TranslatedRoute;
use App\Search\Sapi3\SearchService;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Carbon\Carbon;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Ramsey\Uuid\Uuid;

final class IntegrationController extends Controller
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly IntegrationUrlRepository $integrationUrlRepository,
        private readonly ContactRepository $contactRepository,
        private readonly ContactKeyVisibilityRepository $contactKeyVisibilityRepository,
        private readonly OrganizationRepository $organizationRepository,
        private readonly UdbOrganizerRepository $organizerRepository,
        private readonly CouponRepository $couponRepository,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly UiTiDv1ConsumerRepository $uitidV1ConsumerRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly KeyVisibilityUpgradeRepository $keyVisibilityUpgradeRepository,
        private readonly SearchService $searchClient,
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
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationIds($integrationIds);

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrationsData->collection->map(fn (Integration $integration) => $integration->toArray()),
            'credentials' => [
                'auth0' => $auth0Clients,
                'uitidV1' => $uitidV1Consumers,
                'keycloak' => $keycloakClients,
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

    public function store(StoreIntegrationRequest $request): RedirectResponse
    {
        $guardCouponResult = $this->guardCoupon($request);
        if ($guardCouponResult !== null) {
            return $guardCouponResult;
        }

        $integration = StoreIntegrationMapper::map($request, $this->currentUser);
        $integration = $integration->withKeyVisibility($this->getKeyVisibility($integration));

        if ($request->filled('coupon')) {
            $this->integrationRepository->saveWithCoupon($integration, $request->input('coupon'));
        } else {
            $this->integrationRepository->save($integration);
        }

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $integration->id,
            ]
        );
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        try {
            $this->integrationRepository->deleteById(Uuid::fromString($id));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
        }

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.index')
        );
    }

    public function update(UpdateIntegrationRequest $request, string $id): RedirectResponse
    {
        $currentIntegration = $this->integrationRepository->getById(Uuid::fromString($id));

        $updatedIntegration = UpdateIntegrationMapper::map($request, $currentIntegration);

        $this->integrationRepository->update($updatedIntegration);

        return Redirect::back();
    }

    private function getExpirationDateForOldCredentials(?KeyVisibilityUpgrade $upgrade): ?string
    {
        $createdAt = $upgrade?->getCreatedAt();
        if ($createdAt === null) {
            return null;
        }
        $expirationAmountInDays = config('key_visibility.oldCredentialsExpirationAmountInDays');
        $expirationDate = (new Carbon($createdAt))->addDays($expirationAmountInDays);
        return $expirationDate->toISOString();
    }

    public function show(string $id): Response
    {
        $integration = $this->integrationRepository->getById(Uuid::fromString($id));
        $oldCredentialsExpirationDate = $this->getExpirationDateForOldCredentials($integration->getKeyVisibilityUpgrade());

        $organizers = $this->getIntegrationOrganizersWithTestOrganizer($integration);

        return Inertia::render('Integrations/Detail', [
            'integration' => $integration->toArray(),
            'oldCredentialsExpirationDate' => $oldCredentialsExpirationDate,
            'email' => Auth::user()?->email,
            'subscriptions' => $this->subscriptionRepository->all(),
            'organizers' => $organizers,
        ]);
    }

    public function storeUrl(StoreIntegrationUrlRequest $request, string $id): RedirectResponse
    {
        $integrationUrl = StoreIntegrationUrlMapper::map($request, $id);

        $this->integrationUrlRepository->save($integrationUrl);

        return Redirect::back();
    }

    public function destroyUrl(Request $request, string $id, string $urlId): RedirectResponse
    {
        try {
            $this->integrationUrlRepository->deleteById(Uuid::fromString($urlId));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
        }

        return Redirect::back();
    }

    public function updateUrls(UpdateIntegrationUrlsRequest $request, string $id): RedirectResponse
    {
        $currentUrls = $this->integrationUrlRepository->getByIntegrationId(Uuid::fromString($id));
        $updatedUrls = UpdateIntegrationUrlsMapper::map($request, $currentUrls, Uuid::fromString($id));

        $this->integrationUrlRepository->updateUrls($updatedUrls);

        $toDeleteUrlIds = $currentUrls
            ->filter(
                fn (IntegrationUrl $url) => $updatedUrls->doesntContain('id', '=', $url->id)
            )
            ->map(fn (IntegrationUrl $url) => $url->id);

        $this->integrationUrlRepository->deleteByIds($toDeleteUrlIds);

        return Redirect::back();
    }

    public function updateContacts(string $id, UpdateContactInfoRequest $request): RedirectResponse
    {
        $contacts = UpdateContactInfoMapper::map($request, $id);
        try {
            DB::transaction(function () use ($contacts) {
                foreach ($contacts as $contact) {
                    $this->contactRepository->save($contact);
                }
            });
        } catch (UniqueConstraintViolationException $exception) {
            return Redirect::back()->withErrors(['duplicate_contact' => __('errors.contact.duplicate')]);
        }

        $redirect = $this->guardUserIsContact($request, $id);

        if ($redirect !== null) {
            return $redirect;
        }

        return Redirect::back();
    }

    public function storeContact(StoreContactRequest $request, string $id): RedirectResponse
    {
        $contact = StoreContactMapper::map($request, Uuid::fromString($id));
        try {
            $this->contactRepository->save($contact);
        } catch (UniqueConstraintViolationException) {
            return Redirect::back()->withErrors(['duplicate_contact' => __('errors.contact.duplicate')]);
        }

        return Redirect::back();
    }

    public function deleteContact(Request $request, string $id, string $contactId): RedirectResponse
    {
        try {
            $this->contactRepository->delete(Uuid::fromString($contactId));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
        }

        $redirect = $this->guardUserIsContact($request, $id);

        if ($redirect !== null) {
            return $redirect;
        }

        return Redirect::back();
    }

    public function updateOrganization(string $id, UpdateOrganizationRequest $request): RedirectResponse
    {
        $organization = OrganizationMapper::mapUpdate($request);

        $this->organizationRepository->save($organization);

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $id,
            ]
        );
    }

    public function updateOrganizers(string $integrationId, UpdateIntegrationUdbOrganizersRequest $request): RedirectResponse
    {
        $integration = $this->integrationRepository->getById(Uuid::fromString($integrationId));

        $organizerIds = collect($integration->udbOrganizers())->map(fn (UdbOrganizer $organizer) => $organizer->organizerId);
        $newOrganizers = array_filter(
            UdbOrganizerMapper::mapUpdateOrganizers($request, $integrationId),
            fn (UdbOrganizer $organizer) => !in_array($organizer->organizerId, $organizerIds->toArray(), true)
        );

        $this->organizerRepository->createInBulk(new UdbOrganizers($newOrganizers));

        return Redirect::back();
    }

    public function deleteOrganizer(string $integrationId, string $organizerId): RedirectResponse
    {
        $this->organizerRepository->delete(new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::fromString($integrationId),
            $organizerId
        ));

        return Redirect::back();
    }

    public function requestActivation(string $id, RequestActivationRequest $request): RedirectResponse
    {
        $guardCouponResult = $this->guardCoupon($request);
        if ($guardCouponResult !== null) {
            return $guardCouponResult;
        }

        $organization = OrganizationMapper::mapActivationRequest($request);
        $this->organizationRepository->save($organization);

        $this->integrationRepository->requestActivation(
            Uuid::fromString($id),
            $organization->id,
            $request->input('coupon'),
            UdbOrganizerMapper::mapActivationRequest($request, $id)
        );

        return Redirect::back();
    }

    public function showWidget(string $id): RedirectResponse
    {
        $integration = $this->integrationRepository->getById(Uuid::fromString($id));
        return redirect()->away(ProjectAanvraagUrl::getForIntegration($integration));
    }

    public function storeKeyVisibilityUpgrade(string $id, KeyVisibilityUpgradeRequest $request): RedirectResponse
    {
        $this->keyVisibilityUpgradeRepository->save(KeyVisibilityUpgradeMapper::map($request, Uuid::fromString($id)));

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $id,
            ]
        );
    }

    private function getKeyVisibility(Integration $integration): KeyVisibility
    {
        $contacts = new Collection($integration->contacts());
        $contributor = $contacts->firstWhere('type', ContactType::Contributor);

        if ($contributor === null) {
            return KeyVisibility::v2;
        }

        return $this->contactKeyVisibilityRepository->findByEmail($contributor->email);
    }

    private function guardCoupon(Request $request): ?RedirectResponse
    {
        if (!$request->filled('coupon')) {
            return null;
        }

        try {
            $coupon = $this->couponRepository->getByCode($request->input('coupon'));
        } catch (ModelNotFoundException) {
            return Redirect::back()->withErrors([
                'coupon' => __('errors.coupon.invalid'),
            ]);
        }

        if ($coupon->integrationId !== null) {
            return Redirect::back()->withErrors(['coupon' => __('errors.coupon.already_used')]);
        }

        return null;
    }

    private function guardUserIsContact(Request $request, string $integrationId): ?RedirectResponse
    {
        $contacts = $this->contactRepository->getByIntegrationIdAndEmail(Uuid::fromString($integrationId), $this->currentUser->email());

        if ($contacts->count() === 0) {
            return Redirect::route(
                TranslatedRoute::getTranslatedRouteName($request, 'integrations.index')
            );
        }

        return null;
    }


    public function getIntegrationOrganizersWithTestOrganizer(Integration $integration): Collection
    {
        $organizerIds = collect($integration->udbOrganizers())->map(fn (UdbOrganizer $organizer) => $organizer->organizerId);
        $uitpasOrganizers = $this->searchClient->findUiTPASOrganizers(...$organizerIds)->getMember()?->getItems();

        $organizers = collect($uitpasOrganizers)->map(function (SapiOrganizer $organizer) {
            $id = explode('/', $organizer->getId() ?? '');
            $id = $id[count($id) - 1];

            return [
                'id' => $id,
                'name' => $organizer->getName()?->getValues() ?? [],
                'status' => 'Live',
            ];
        });

        $organizers->push([
            'id' => '0ce87cbc-9299-4528-8d35-92225dc9489f',
            'name' => ['nl' => 'UiTPAS Organisatie (Regio Gent + Paspartoe)'],
            'status' => 'Test',
        ]);

        return $organizers;
    }
}
