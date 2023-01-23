<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Contact;
use Illuminate\Support\Arr;

final class InsightlyContactLink implements ContactLink
{
    public function __construct(private readonly InsightlyClient $insightlyClient)
    {
    }

    public function link(Contact $contact): int
    {
        $contactIds = $this->insightlyClient->contacts()->findIdsByEmail($contact->email);

        if (empty($contactIds)) {
            return $this->insightlyClient->contacts()->create($contact);
        }

        $contactIds = array_values(Arr::sort($contactIds));
        return $contactIds[0];
    }
}
