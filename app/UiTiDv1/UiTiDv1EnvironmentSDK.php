<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use App\Domain\Integrations\Integration;
use App\Json;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

final class UiTiDv1EnvironmentSDK
{
    public function __construct(
        public readonly UiTiDv1Environment $environment,
        private readonly ClientInterface $httpClient,
        private readonly array $permissionGroupsPerIntegrationType
    ) {}

    public function createConsumerForIntegration(Integration $integration): UiTiDv1Consumer
    {
        $name = sprintf('%s (id: %s)', $integration->name, $integration->id->toString());

        $formData = [
            'name' => $name,
            'description' => $integration->description,
            'group' => $this->permissionGroupsPerIntegrationType[$integration->type->value] ?? [],
        ];

        $response = $this->sendPostRequest('serviceconsumer', $formData);

        // Easiest way to convert XML to an array is by encoding an SimpleXMLELement as JSON and then decoding it again.
        // This way we don't need to deal with XPath to read the values.
        $xml = new SimpleXMLElement($response->getBody()->getContents());
        $data = Json::decodeAssociatively(Json::encode($xml));
        $consumerKey = $data['consumerKey'];
        $consumerSecret = $data['consumerSecret'];
        $apiKey = $data['apiKeySapi3'];

        return new UiTiDv1Consumer($integration->id, $consumerKey, $consumerSecret, $apiKey, $this->environment);
    }

    public static function createOAuth1HttpClient(
        string $baseUrl,
        string $consumerKey,
        string $consumerSecret,
    ): ClientInterface {
        $handlerStack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token' => '',
            'token_secret' => ''
        ]);
        $handlerStack->push($middleware);

        // Make sure the base URL always has a single trailing slash.
        $baseUrl = rtrim($baseUrl, '/') . '/';

        return new Client(
            [
                'base_uri' => $baseUrl,
                'handler' => $handlerStack,
                'auth' => 'oauth'
            ]
        );
    }

    private function sendPostRequest(string $path, array $formData): ResponseInterface
    {
        // Make sure to encode the form data using Query::build() and use the "body" option, as opposed to using the
        // "form_params" option. While form_params supports parameters with multiple values, it encodes them with a []
        // suffix which is not supported by UiTiD v1.
        $options = [
            'http_errors' => false,
            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
            'body' => Query::build($formData),
        ];
        $response = $this->httpClient->request('POST', $path, $options);

        $status = $response->getStatusCode();
        if ($status < 200 || $status > 299) {
            throw UiTiDv1SDKException::forResponse($response);
        }

        return $response;
    }
}
