<?php

declare(strict_types=1);

namespace Tests\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Insightly\InsightlyClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class InsightlyClientTest extends TestCase
{
    private InsightlyClient $insightlyClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insightlyClient = new InsightlyClient(
            new Client(
                [
                    'base_uri' => Config::get('insightly.host'),
                    'http_errors' => false,
                ]
            ),
            Config::get('insightly.api_key')
        );
    }

    /**
     * @test
     */
    public function it_can_create_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            ContactType::Technical,
            'Jane',
            'Doe',
            'jane.doe@anonymous.com'
        );

        $contactId = $this->insightlyClient->contacts()->create($contact);
        $this->assertNotNull($contactId);

        $this->insightlyClient->contacts()->delete($contactId);
    }
}
