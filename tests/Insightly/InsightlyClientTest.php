<?php

declare(strict_types=1);

namespace Tests\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use GuzzleHttp\Client;
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
                    'base_uri' => config('insightly.host'),
                    'http_errors' => false,
                ]
            ),
            config('insightly.api_key'),
            new Pipelines(config('insightly.pipelines'))
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

    /**
     * @test
     */
    public function it_can_create_an_opportunity(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            []
        );

        $contactId = $this->insightlyClient->opportunities()->create($integration);
        $this->assertNotNull($contactId);

        $this->insightlyClient->opportunities()->delete($contactId);
    }
}
