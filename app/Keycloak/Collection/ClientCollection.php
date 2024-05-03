<?php

declare(strict_types=1);

namespace App\Keycloak\Collection;

use App\Keycloak\Dto\Client;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Client>
 */
final class ClientCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Client::class;
    }

}
