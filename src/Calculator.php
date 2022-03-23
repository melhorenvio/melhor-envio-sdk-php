<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use MelhorEnvio\Enums\Endpoint;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\CalculatorInterface;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Calculator as CalculatorShipmentSDK;
use MelhorEnvio\Resources\Resource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class Calculator extends CalculatorShipmentSDK
{
    protected Resource $resource;

    protected static $tries = 0;

    public function __construct(Resource $resource)
    {
        parent::__construct($resource);

        $this->resource = $resource;

        $this->stack = HandlerStack::create();

        $this->stack->push($this->middlewareRefreshToken());

        $this->resource->setHttp(
            $this->client($this->resource->token)
        );
    }

    public function middlewareRefreshToken()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);
                return $promise->then(
                    function (ResponseInterface $response) use ($request, $options) {
                        if ($response->getStatusCode() === 401) {
                            $request = $this->updateToken($request);
                            return $this->resource->getHttp()->sendAsync($request, $options);
                        }
                        return $response;
                    }
                );
            };
        };
    }

    private function updateToken(RequestInterface $request)
    {
        $token = $this->handleRefreshToken();
        return \GuzzleHttp\Psr7\modify_request($request, [
            'set_headers' => [
                'Authorization' => 'Bearer ' . $token['access_token'],
            ],
        ]);
    }

    /**
     * @throws InvalidCalculatorPayloadException|CalculatorException
     */
    public function calculate()
    {
        parent::validatePayload();
        try {
            $response = $this->resource->getHttp()->post('me/shipment/calculate', [
                'json' => $this->payload,
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $exception) {
            //todo: make exception.
        }
    }

    public function client(String $token): Client
    {
        return new Client([
            'handler' => $this->stack,
            'base_uri' => Endpoint::ENDPOINTS[$this->resource->getEnvironment()] . '/api/' . Endpoint::VERSIONS[$this->resource->getEnvironment()] . '/',
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);
    }

    public function handleRefreshToken(): array
    {
        $provider = new OAuth2(
            $this->resource->getAppId(),
            $this->resource->getAppSecret(),
            $this->resource->getAppRedirectUri()
        );
        return $provider->refreshToken($this->resource->refreshToken);
    }
}
