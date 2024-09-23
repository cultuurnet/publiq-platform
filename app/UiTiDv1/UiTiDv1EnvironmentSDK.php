<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use App\Domain\Integrations\Integration;
use App\Json;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;

final class UiTiDv1EnvironmentSDK
{
    public function __construct(
        public readonly UiTiDv1Environment $environment,
        private readonly ClientInterface $httpClient,
        private readonly array $permissionGroupsPerIntegrationType
    ) {
    }

    public function createConsumerForIntegration(Integration $integration): UiTiDv1Consumer
    {
        $formData = [
            'name' => $this->consumerName($integration),
            'group' => $this->permissionGroupsPerIntegrationType[$integration->type->value] ?? [],
        ];

        $response = $this->sendPostRequest('serviceconsumer', $formData);

        // Easiest way to convert XML to an array is by encoding an SimpleXMLElement as JSON and then decoding it again.
        // This way we don't need to deal with XPath to read the values.
        $xml = new SimpleXMLElement($response->getBody()->getContents());
        $data = Json::decodeAssociatively(Json::encode($xml));
        $consumerId = (string) $data['id'];
        $consumerKey = $data['consumerKey'];
        $apiKey = $data['apiKeySapi3'];

        return new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integration->id,
            $consumerId,
            $consumerKey,
            $apiKey,
            $this->environment
        );
    }

    public function updateConsumerForIntegration(Integration $integration, UiTiDv1Consumer $consumer): void
    {
        $formData = [
            'name' => $this->consumerName($integration),
            'group' => $this->permissionGroupsPerIntegrationType[$integration->type->value] ?? [],
        ];

        $this->sendPostRequest('serviceconsumer/' . $consumer->consumerKey, $formData);
    }

    public function blockConsumer(Integration $integration, UiTiDv1Consumer $consumer): void
    {
        $this->sendPostRequest('serviceconsumer/' . $consumer->consumerKey, [
            'status' => UiTiDv1ConsumerStatus::Blocked->value,
            'group' => $this->permissionGroupsPerIntegrationType[$integration->type->value] ?? [],
        ]);
    }

    public function unblockConsumer(Integration $integration, UiTiDv1Consumer $consumer): void
    {
        $this->sendPostRequest('serviceconsumer/' . $consumer->consumerKey, [
            'status' => UiTiDv1ConsumerStatus::Active->value,
            'group' => $this->permissionGroupsPerIntegrationType[$integration->type->value] ?? [],
        ]);
    }

    public function fetchStatusOfConsumer(UiTiDv1Consumer $consumer): UiTiDv1ConsumerStatus
    {
        $response = $this->httpClient->request('GET', 'serviceconsumer/' . $consumer->consumerKey);

        if ($response->getStatusCode() !== 200) {
            return UiTiDv1ConsumerStatus::Unknown;
        }

        $body = $response->getBody()->getContents();
        $xml = simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement || ! $xml->status instanceof SimpleXMLElement) {
            return UiTiDv1ConsumerStatus::Unknown;
        }

        return UiTiDv1ConsumerStatus::tryFrom($xml->status->__toString()) ?? UiTiDv1ConsumerStatus::Unknown;
    }

    public static function createOAuth1HttpClient(
        string $baseUrl,
        string $consumerKey,
        string $consumerSecret,
    ): ClientInterface {
        $handlerStack = HandlerStack::create();
        $middleware = new OAuth1([
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token' => '',
            'token_secret' => '',
        ]);
        $handlerStack->push($middleware);

        // Make sure the base URL always has a single trailing slash.
        $baseUrl = rtrim($baseUrl, '/') . '/';

        return new Client(
            [
                'base_uri' => $baseUrl,
                'handler' => $handlerStack,
                'auth' => 'oauth',
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

    private function consumerName(Integration $integration): string
    {
        return $integration->name . ' (via publiq platform)';
    }
}
