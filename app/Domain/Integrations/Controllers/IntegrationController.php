<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\FormRequests\StoreIntegration;
use App\Domain\Integrations\FormRequests\UpdateBasicInfo;
use App\Domain\Integrations\FormRequests\UpdateBillingInfo;
use App\Domain\Integrations\FormRequests\UpdateContactInfo;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\Router\TranslatedRoute;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        private readonly ContactRepository $contactRepository,
        private readonly OrganizationRepository $organizationRepository,
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
        $integration = StoreIntegrationMapper::map($storeIntegration, $this->currentUser);
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
            ),
            [],
            303
        );
    }

    public function update(Request $request, string $id, UpdateBasicInfo $updateInfo): RedirectResponse
    {
        $this->integrationRepository->update(Uuid::fromString($id), $updateInfo);

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName(
                request: $request,
                routeName: 'integrations.detail'
            ),
            [
                'id' => $id,
            ],
            303
        );
    }

    public function updateContacts(string $id, UpdateContactInfo $updateContactInfo): RedirectResponse
    {
        DB::transaction(function () use ($id, $updateContactInfo) {
            if ($updateContactInfo->input('functional.id') !== null) {
                $contactId = $updateContactInfo->input('functional.id');

                $contact = new Contact(
                    Uuid::fromString($contactId),
                    Uuid::fromString($id),
                    $updateContactInfo->input('functional.email'),
                    ContactType::from($updateContactInfo->input('functional.type')),
                    $updateContactInfo->input('functional.firstName'),
                    $updateContactInfo->input('functional.lastName')
                );

                $this->contactRepository->save($contact);
            }
            if ($updateContactInfo->input('technical.id') !== null) {
                $contactId = $updateContactInfo->input('technical.id');

                $contact = new Contact(
                    Uuid::fromString($contactId),
                    Uuid::fromString($id),
                    $updateContactInfo->input('technical.email'),
                    ContactType::from($updateContactInfo->input('technical.type')),
                    $updateContactInfo->input('technical.firstName'),
                    $updateContactInfo->input('technical.lastName')
                );

                $this->contactRepository->save($contact);
            }

            $contributors = $updateContactInfo->input('contributors');

            foreach ($contributors as $contributor) {
                $contactId = $contributor['id'];

                $contact = new Contact(
                    Uuid::fromString($contactId),
                    Uuid::fromString($id),
                    $contributor['email'],
                    ContactType::from($contributor['type']),
                    $contributor['firstName'],
                    $contributor['lastName']
                );

                $this->contactRepository->save($contact);
            }

            $newLastName = $updateContactInfo->input('newContributorLastName');
            $newFirstName = $updateContactInfo->input('newContributorFirstName');
            $newEmail = $updateContactInfo->input('newContributorEmail');

            if ($newLastName !== null && $newFirstName !== null && $newEmail !== null) {
                $contact = new Contact(
                    Uuid::uuid4(),
                    Uuid::fromString($id),
                    $newEmail,
                    ContactType::Contributor,
                    $newFirstName,
                    $newLastName
                );

                $this->contactRepository->save($contact);


            }

        });

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName(
                request: $updateContactInfo,
                routeName: 'integrations.detail'
            ),
            [
                'id' => $id,
            ],
            303
        );
    }

    public function deleteContact(Request $request, string $id, string $contactId): RedirectResponse
    {
        try {
            $this->contactRepository->delete(Uuid::fromString($contactId));
        } catch (ModelNotFoundException) {
            // We can redirect back to integrations, even if not successful
        }

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName(
                request: $request,
                routeName: 'integrations.detail'
            ),
            ['id' => $id],
            303
        );
    }

    public function updateBilling(string $id, UpdateBillingInfo $updateBillingInfo): RedirectResponse
    {
        $organisation = new Organization(
            Uuid::fromString($updateBillingInfo->input('organisation.id')),
            $updateBillingInfo->input('organisation.name'),
            'test@test.be',
            $updateBillingInfo->input('organisation.vat'),
            new Address(
                $updateBillingInfo->input('organisation.address.street'),
                $updateBillingInfo->input('organisation.address.zip'),
                $updateBillingInfo->input('organisation.address.city'),
                $updateBillingInfo->input('organisation.address.country'),
            )
        );

        $this->organizationRepository->save($organisation);

        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName(
                request: $updateBillingInfo,
                routeName: 'integrations.detail'
            ),
            [
                'id' => $id,
            ],
            303
        );
    }

    public function detail(string $id): Response
    {
        try {
            $integration = $this->integrationRepository->getById(Uuid::fromString($id));
            $subscription = $this->subscriptionRepository->getById($integration->subscriptionId);
            $contacts = $this->contactRepository->getByIntegrationId(UUid::fromString($id));
        } catch (Throwable) {
            abort(404);
        }

        return Inertia::render('Integrations/Detail', [
            'integration' => [
                ...$integration->toArray(),
                'contacts' => $contacts->toArray(),
                'urls' => $integration->urls(),
                'organisation' => $integration->organization(),
                'subscription' => $subscription,
            ],
        ]);
    }
}
