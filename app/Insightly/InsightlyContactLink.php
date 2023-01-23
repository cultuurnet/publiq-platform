<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Contact;

final class InsightlyContactLink implements ContactLink
{
    public function link(Contact $contact): int
    {
        // TODO: Implement link() method.
    }
}
