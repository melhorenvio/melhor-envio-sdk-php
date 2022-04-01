<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\RequestException;
use GuzzleHttp\Exception\ConnectException;
use MelhorEnvio\Enums\Endpoint;
use MelhorEnvio\Shipment as ShipmentSDK;
use MelhorEnvio\MelhorEnvioSdkPhp\Calculator;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\ShipmentInterface;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class Shipment extends ShipmentSDK implements ShipmentInterface
{
    protected const MAX_RETRIES = 2;

    public OAuth2 $oAuth2;

    protected $accessToken;

    protected String $refreshToken;

    public function __construct(OAuth2 $oAuth2, string $accessToken, String $refreshToken)
    {
        parent::__construct($accessToken, $oAuth2->getEnvironment());

        $this->oAuth2 = $oAuth2;

        $this->accessToken = $accessToken;

        $this->refreshToken = $refreshToken;

        $this->stack = HandlerStack::create();

        $this->stack->push($this->middlewareRefreshToken());

        $this->stack->push(
            Middleware::retry($this->retryDecider(), $this->retryDelay())
        );

        $this->setHttp($this->client());
    }

    public function client(): Client
    {
        return new Client([
            'handler' => $this->stack,
            'base_uri' => Endpoint::ENDPOINTS[$this->getEnvironment()] . '/api/' . Endpoint::VERSIONS[$this->getEnvironment()] . '/',
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
            ]
        ]);
    }

    protected function retryDecider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            
            if ($retries >= self::MAX_RETRIES) {
                return false;
            }
            
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        };
    }

    protected function retryDelay()
    {
        return function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        };
    }

    protected function middlewareRefreshToken()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);
                return $promise->then(
                    function (ResponseInterface $response) use ($request, $options) {
                        if ($response->getStatusCode() === 401) {
                            $request = $this->updateToken($request);
                            return $this->getHttp()->sendAsync($request, $options);
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

    public function handleRefreshToken(): array
    {
        return $this->oAuth2->refreshToken($this->refreshToken);
    }
}
