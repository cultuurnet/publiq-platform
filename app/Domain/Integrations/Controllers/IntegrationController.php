<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\FormRequests\ActivateWithCouponRequest;
use App\Domain\Integrations\FormRequests\CreateOrganizationRequest;
use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\FormRequests\StoreIntegrationUrlRequest;
use App\Domain\Integrations\FormRequests\UpdateContactInfoRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlsRequest;
use App\Domain\Integrations\FormRequests\UpdateOrganizationRequest;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\Mappers\OrganizationMapper;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use App\Domain\Integrations\Mappers\StoreIntegrationUrlMapper;
use App\Domain\Integrations\Mappers\UpdateContactInfoMapper;
use App\Domain\Integrations\Mappers\UpdateIntegrationMapper;
use App\Domain\Integrations\Mappers\UpdateIntegrationUrlsMapper;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\IntegrationUrlRepository;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\ProjectAanvraag\ProjectAanvraagUrl;
use App\Router\TranslatedRoute;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Ramsey\Uuid\Uuid;
use Throwable;

final class IntegrationController extends Controller
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly IntegrationUrlRepository $integrationUrlRepository,
        private readonly ContactRepository $contactRepository,
        private readonly OrganizationRepository $organizationRepository,
        private readonly CouponRepository $couponRepository,
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

    public function store(StoreIntegrationRequest $request): RedirectResponse
    {
        $integration = StoreIntegrationMapper::map($request, $this->currentUser);
        $this->integrationRepository->save($integration);

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.index')
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

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $id,
            ]
        );
    }

    public function show(string $id): Response
    {
        try {
            $integration = $this->integrationRepository->getById(Uuid::fromString($id));
            $subscription = $this->subscriptionRepository->getById($integration->subscriptionId);
            $contacts = $this->contactRepository->getByIntegrationId(UUid::fromString($id));
            $authClients = $this->auth0ClientRepository->getDistributedByIntegrationId(UUid::fromString($id));
            $legacyAuthConsumers = $this->uitidV1ConsumerRepository->getDistributedByIntegrationId(UUid::fromString($id));

        } catch (Throwable) {
            abort(404);
        }

        return Inertia::render('Integrations/Detail', [
            'integration' => [
                ...$integration->toArray(),
                'contacts' => $contacts->toArray(),
                'urls' => $integration->urls(),
                'organization' => $integration->organization(),
                'subscription' => $subscription,
                'authClients' => $authClients,
                'legacyAuthConsumers' => $legacyAuthConsumers,
            ],
            'email' => Auth::user()?->email,
        ]);
    }

    public function storeUrl(StoreIntegrationUrlRequest $request, string $id): RedirectResponse
    {
        $integrationUrl = StoreIntegrationUrlMapper::map($request, $id);

        $this->integrationUrlRepository->save($integrationUrl);

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $id,
            ]
        );
    }

    public function destroyUrl(Request $request, string $id, string $urlId): RedirectResponse
    {
        try {
            $this->integrationUrlRepository->deleteById(Uuid::fromString($urlId));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
        }

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $id,
            ]
        );
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

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'integrations.show'),
            [
                'id' => $id,
            ]
        );
    }

    public function updateContacts(string $id, UpdateContactInfoRequest $request): RedirectResponse
    {
        $contacts = UpdateContactInfoMapper::map($request, $id);

        DB::transaction(function () use ($contacts) {
            foreach ($contacts as $contact) {
                $this->contactRepository->save($contact);
            }
        });

        return Redirect::back();
    }

    public function deleteContact(Request $request, string $id, string $contactId): RedirectResponse
    {
        try {
            $this->contactRepository->delete(Uuid::fromString($contactId));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
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

    public function requestActivation(string $id, RequestActivationRequest $request): RedirectResponse
    {
        if ($request->filled('coupon')) {
            try {
                $coupon = $this->couponRepository->getByCode($request->input('coupon'));
                if ($coupon->isDistributed) {
                    return Redirect::back()->withErrors(['coupon' => 'Coupon is already used']);
                }
            } catch (ModelNotFoundException) {
                return Redirect::back()->withErrors([
                    'coupon' => 'Invalid coupon',
                ]);
            }
        }

        $organization = OrganizationMapper::mapActivationRequest($request);
        $this->organizationRepository->save($organization);

        $this->integrationRepository->requestActivation(Uuid::fromString($id), $organization->id, $request->input('coupon'));

        return Redirect::back();
    }

    // @deprecated
    public function activateWithCoupon(string $id, ActivateWithCouponRequest $request): RedirectResponse
    {
        try {
            $coupon = $this->couponRepository->getByCode($request->input('coupon'));
            if ($coupon->isDistributed) {
                return Redirect::back()->withErrors(['coupon' => 'Coupon is already used']);
            }

            $this->integrationRepository->activateWithCouponCode(Uuid::fromString($id), $request->input('coupon'));

            return Redirect::back();

        } catch (ModelNotFoundException $exception) {
            return Redirect::back()->withErrors([
                'coupon' => 'Invalid coupon',
            ]);
        }
    }

    // @deprecated
    public function activateWithOrganization(string $id, CreateOrganizationRequest $request): RedirectResponse
    {
        $organization = OrganizationMapper::mapCreate($request);

        $this->organizationRepository->save($organization);

        $this->integrationRepository->activateWithOrganization(Uuid::fromString($id), $organization->id);

        return Redirect::back();
    }

    public function showWidget(string $id): RedirectResponse
    {
        $integration = $this->integrationRepository->getById(Uuid::fromString($id));
        return redirect()->away(ProjectAanvraagUrl::getForIntegration($integration));
    }
}
