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
    private const INTEGRATION_ID = '965a5b22-bcb6-4b93-a78b-229e4667a52d';
    private const FIRST_LOGIN_URL_ID = 'e9bcfca2-f6ee-404f-aca3-372eacf72b7f';
    private const SECOND_LOGIN_URL_ID = '7d6488d5-6390-4da5-9ab0-7f50939c9c32';
    private const FIRST_CALLBACK_URL_ID = '0d92e499-1ead-44f1-9bec-9901d638626e';
    private const SECOND_CALLBACK_URL_ID = '044683b8-d689-4900-a135-9873161e145f';
    private const FIRST_LOGOUT_URL_ID = '9ca543c3-a695-4403-adc1-a1159e6ae0a5';
    private const SECOND_LOGOUT_URL_ID = '5a199895-878f-4987-bafd-df2a69e0dcf4';

    private function getInputsForFullUpdate(): array
    {
        return [
            'loginUrls' => [
            [
                'id' => self::FIRST_LOGIN_URL_ID,
                'url' => 'https://publiqtest.be/login-1-new',
            ],
            [
                'id' => self::SECOND_LOGIN_URL_ID,
                'url' => 'https://publiqtest.be/login-2-new',
            ],
            ],
            'callbackUrls' => [
                [
                    'id' => self::FIRST_CALLBACK_URL_ID,
                    'url' => 'https://publiqtest.be/callback-1-new',
                ],
                [
                    'id' => self::SECOND_CALLBACK_URL_ID,
                    'url' => 'https://publiqtest.be/callback-2-new',
                ],
            ],
            'logoutUrls' => [
                [
                    'id' => self::FIRST_LOGOUT_URL_ID,
                    'url' => 'https://publiqtest.be/logout-1-new',
                ],
                [
                    'id' => self::SECOND_LOGOUT_URL_ID,
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
                Uuid::fromString(self::FIRST_LOGIN_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/login-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_LOGIN_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Login,
                'https://publiqtest.be/login-2-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::FIRST_CALLBACK_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/callback-1'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_CALLBACK_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/callback-2'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::FIRST_LOGOUT_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-1'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_LOGOUT_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
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
                Uuid::fromString(self::FIRST_LOGIN_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/login-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_LOGIN_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Login,
                'https://publiqtest.be/login-2-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::FIRST_CALLBACK_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/callback-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_CALLBACK_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/callback-2-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::FIRST_LOGOUT_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_LOGOUT_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
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
            'loginUrls' => [
                ['id' => self::FIRST_LOGIN_URL_ID,
                'url' => 'https://publiqtest.be/login-1-new'],
                ['id' => self::SECOND_LOGIN_URL_ID,
                'url' => 'https://publiqtest.be/login-2-new'],
            ],
        ];

        $request = new UpdateIntegrationUrlsRequest();
        $request->merge($inputs);

        $currentUrls = $this->getCurrentIntegrationUrls();

        $actual = UpdateIntegrationUrlsMapper::map($request, $currentUrls);

        $expected = [
            new IntegrationUrl(
                Uuid::fromString(self::FIRST_LOGIN_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Login,
                'https://publiqtest.be/login-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_LOGIN_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Login,
                'https://publiqtest.be/login-2-new'
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_creates_updated_callback_urls_from_request(): void
    {
        $inputs = [
            'callbackUrls' => [
                [
                    'id' => self::FIRST_CALLBACK_URL_ID,
                    'url' => 'https://publiqtest.be/callback-1-new',
                ],
                [
                    'id' => self::SECOND_CALLBACK_URL_ID,
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
                Uuid::fromString(self::FIRST_CALLBACK_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Callback,
                'https://publiqtest.be/callback-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_CALLBACK_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Callback,
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
                    'id' => self::FIRST_LOGOUT_URL_ID,
                    'url' => 'https://publiqtest.be/logout-1-new',
                ],
                [
                    'id' => self::SECOND_LOGOUT_URL_ID,
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
                Uuid::fromString(self::FIRST_LOGOUT_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Testing,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-1-new'
            ),
            new IntegrationUrl(
                Uuid::fromString(self::SECOND_LOGOUT_URL_ID),
                Uuid::fromString(self::INTEGRATION_ID),
                Environment::Production,
                IntegrationUrlType::Logout,
                'https://publiqtest.be/logout-2-new'
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

}
