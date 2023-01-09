<?php

declare(strict_types=1);

namespace Tests\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Insightly\SyncIsAllowed;
use Iterator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class SyncIsAllowedTest extends TestCase
{
    /**
     * @dataProvider provideCases
     */
    public function test_it_checks_for_contact(ContactType $contactType, bool $isAllowed): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'info@publiq.be',
            $contactType,
            'Jane',
            'Doe'
        );

        $this->assertEquals($isAllowed, SyncIsAllowed::forContact($contact));
    }

    public function provideCases(): Iterator
    {
        yield 'functional' => [
            'contactType' => ContactType::Functional,
            'isAllowed' => true,
        ];

        yield 'technical' => [
            'contactType' => ContactType::Technical,
            'isAllowed' => true,
        ];

        yield 'contributor' => [
            'contactType' => ContactType::Contributor,
            'isAllowed' => false,
        ];
    }
}
