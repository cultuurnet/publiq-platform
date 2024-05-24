<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Website;
use Ramsey\Uuid\Uuid;

final class StoreIntegrationMapper
{
    public static function map(StoreIntegrationRequest $request, CurrentUser $currentUser): Integration
    {
        $integrationId = Uuid::uuid4();

        $functionalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $request->input('emailFunctionalContact'),
            ContactType::Functional,
            $request->input('firstNameFunctionalContact'),
            $request->input('lastNameFunctionalContact')
        );

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $request->input('emailTechnicalContact'),
            ContactType::Technical,
            $request->input('firstNameTechnicalContact'),
            $request->input('lastNameTechnicalContact')
        );

        $contributorContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $currentUser->email(),
            ContactType::Contributor,
            $currentUser->firstName(),
            $currentUser->lastName()
        );

        $integration = new Integration(
            $integrationId,
            IntegrationType::from($request->input('integrationType')),
            $request->input('integrationName'),
            $request->input('description'),
            Uuid::fromString($request->input('subscriptionId')),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );
        $integration = $integration->withContacts($functionalContact, $technicalContact, $contributorContact);

        if (!is_null($request->input('website'))) {
            $integration = $integration->withWebsite(new Website($request->input('website')));
        }

        return $integration;
    }
}
