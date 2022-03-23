<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use MelhorEnvio\Enums\Endpoint;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\CalculatorInterface;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Calculator as CalculatorShipmentSDK;
use MelhorEnvio\Resources\Resource;

class Calculator extends CalculatorShipmentSDK implements CalculatorInterface
{
    protected Resource $resource;

    //todo: make logic tries.

    public function __construct(Resource $resource)
    {
        parent::__construct($resource);

        $this->resource = $resource;
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
            //todo: use middleware Guzzle.
            if ($exception->getCode() == 401) {
                $this->updateResourceHttp();
                return $this->calculate();
            }
        }
    }

    public function updateResourceHttp(): void
    {
        $tokens = $this->handleRefreshToken();
        $this->resource->setHttp(new Client([
            'base_uri' => Endpoint::ENDPOINTS[$this->resource->getEnvironment()] . '/api/' . Endpoint::VERSIONS[$this->resource->getEnvironment()] . '/',
            'headers' => [
                'Authorization' => 'Bearer ' . $tokens['access_token'],
                'Accept' => 'application/json',
            ]
        ]));
    }

    public function handleRefreshToken(): array
    {
        $provider = new OAuth2(
            $this->resource->getAppId(),
            $this->resource->getAppSecret(),
            $this->resource->getAppRedirectUri()
        );

        return $provider->refreshToken();
    }
}
