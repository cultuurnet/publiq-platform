<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlsRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Mappers\UpdateIntegrationUrlsMapper;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UpdateIntegrationUrlsMapperTest extends TestCase
{
    private string $integrationId = '965a5b22-bcb6-4b93-a78b-229e4667a52d';

    private function getInputsForFullUpdate(): array
    {
        return [
            'loginUrl' => [
                'id' => 'e9bcfca2-f6ee-404f-aca3-372eacf72b7f',
                'url' => 'https://publiqtest.be/login-new',
            ],
            'callbackUrls' => [
                [
                    'id' => '0d92e499-1ead-44f1-9bec-9901d638626e',
                    'url' => 'https://publiqtest.be/callback-1-new',
                ],
                [
                    'id' => '044683b8-d689-4900-a135-9873161e145f',
                    'url' => 'https://publiqtest.be/callback-2-new',
                ],
            ],
            'logoutUrls' => [
                [
                    'id' => '9ca543c3-a695-4403-adc1-a1159e6ae0a5',
                    'url' => 'https://publiqtest.be/logout-1-new',
                ],
                [
                    'id' => '5a199895-878f-4987-bafd-df2a69e0dcf4',
                    'url' => 'https://publiqtest.be/logout-2-new',
                ],
            ],
        ];
    }

    /**
     * @return array<IntegrationUrl>
     */
    private function getCurrentIntegrationUrls(): array
    {
        return [
            new IntegrationUrl(
                Uuid::fromString('e9bcfca2-f6ee-404f-aca3-372eacf72b7f'),
                Uuid::fromString($this->integrationId),
                Environment::Production,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/login'
            ),
            new IntegrationUrl(
                Uuid::fromString('0d92e499-1ead-44f1-9bec-9901d638626e'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/callback-1'
            ),
            new IntegrationUrl(
                Uuid::fromString('044683b8-d689-4900-a135-9873161e145f'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/callback-2'
            ),
            new IntegrationUrl(
                Uuid::fromString('9ca543c3-a695-4403-adc1-a1159e6ae0a5'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-1'
            ),
            new IntegrationUrl(
                Uuid::fromString('5a199895-878f-4987-bafd-df2a69e0dcf4'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-2'
            ),
        ];
    }

    /**
     * @return array<IntegrationUrl>
     */
    private function getExpectedIntegrationUrlForFullUpdate(): array
    {
        return [
            new IntegrationUrl(
                Uuid::fromString('e9bcfca2-f6ee-404f-aca3-372eacf72b7f'),
                Uuid::fromString($this->integrationId),
                Environment::Production,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/login-new'
            ),
            new IntegrationUrl(
                Uuid::fromString('0d92e499-1ead-44f1-9bec-9901d638626e'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/callback-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString('044683b8-d689-4900-a135-9873161e145f'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/callback-2-new'
            ),
            new IntegrationUrl(
                Uuid::fromString('9ca543c3-a695-4403-adc1-a1159e6ae0a5'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString('5a199895-878f-4987-bafd-df2a69e0dcf4'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-2-new'
            ),
        ];
    }

    public function test_it_creates_updated_urls_from_request(): void
    {
        $inputs = $this->getInputsForFullUpdate();

        $request = new UpdateIntegrationUrlsRequest();
        $request->merge($inputs);

        $currentUrls = $this->getCurrentIntegrationUrls();

        $actual = UpdateIntegrationUrlsMapper::map($request, $currentUrls);

        $expected = $this->getExpectedIntegrationUrlForFullUpdate();

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_creates_updated_login_integration_url_from_request(): void
    {
        $inputs = [
            'loginUrl' => [
                'id' => 'e9bcfca2-f6ee-404f-aca3-372eacf72b7f',
                'url' => 'https://publiqtest.be/login-new',
            ],
        ];

        $request = new UpdateIntegrationUrlsRequest();
        $request->merge($inputs);

        $currentUrls = $this->getCurrentIntegrationUrls();

        $actual = UpdateIntegrationUrlsMapper::map($request, $currentUrls);

        $expected = [
            new IntegrationUrl(
                Uuid::fromString('e9bcfca2-f6ee-404f-aca3-372eacf72b7f'),
                Uuid::fromString($this->integrationId),
                Environment::Production,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/login-new'
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_creates_updated_callback_urls_from_request(): void
    {
        $inputs = [
            'callbackUrls' => [
                [
                    'id' => '0d92e499-1ead-44f1-9bec-9901d638626e',
                    'url' => 'https://publiqtest.be/callback-1-new',
                ],
                [
                    'id' => '044683b8-d689-4900-a135-9873161e145f',
                    'url' => 'https://publiqtest.be/callback-2-new',
                ],
            ],
        ];

        $request = new UpdateIntegrationUrlsRequest();
        $request->merge($inputs);

        $currentUrls = $this->getCurrentIntegrationUrls();

        $actual = UpdateIntegrationUrlsMapper::map($request, $currentUrls);

        $expected = [
            new IntegrationUrl(
                Uuid::fromString('0d92e499-1ead-44f1-9bec-9901d638626e'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/callback-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString('044683b8-d689-4900-a135-9873161e145f'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/callback-2-new'
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_creates_updated_logout_urls_from_request(): void
    {
        $inputs = [
            'logoutUrls' => [
                [
                    'id' => '9ca543c3-a695-4403-adc1-a1159e6ae0a5',
                    'url' => 'https://publiqtest.be/logout-1-new',
                ],
                [
                    'id' => '5a199895-878f-4987-bafd-df2a69e0dcf4',
                    'url' => 'https://publiqtest.be/logout-2-new',
                ],
            ],
        ];

        $request = new UpdateIntegrationUrlsRequest();
        $request->merge($inputs);

        $currentUrls = $this->getCurrentIntegrationUrls();

        $actual = UpdateIntegrationUrlsMapper::map($request, $currentUrls);

        $expected = [
            new IntegrationUrl(
                Uuid::fromString('9ca543c3-a695-4403-adc1-a1159e6ae0a5'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString('5a199895-878f-4987-bafd-df2a69e0dcf4'),
                Uuid::fromString($this->integrationId),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-2-new'
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

}
