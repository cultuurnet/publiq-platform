<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\StoreIntegration;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use Ramsey\Uuid\Uuid;

final class StoreIntegrationMapper
{
    static function map(StoreIntegration $storeIntegration, CurrentUser $currentUser): Integration {
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
            $currentUser->email(),
            ContactType::Contributor,
            $currentUser->firstName(),
            $currentUser->lastName()
        );


        return (new Integration(
            $integrationId,
            IntegrationType::from($storeIntegration->input('integrationType')),
            $storeIntegration->input('integrationName'),
            $storeIntegration->input('description'),
            Uuid::fromString($storeIntegration->input('subscriptionId')),
            IntegrationStatus::Draft
        ))->withContacts($contactOrganization, $contactPartner, $contributor);
    }
}
