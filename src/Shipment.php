<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MelhorEnvio\Enums\Endpoint;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\ShipmentInterface;
use MelhorEnvio\Shipment as ShipmentSDK;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

        $this->setHttp($this->makeClient());
    }

    public function setEnvironment(string $environment): void
    {
        parent::setEnvironment($environment);

        $this->setHttp($this->makeClient());
    }

    public function makeClient(array $extraOptions = []): Client
    {
        $stack = $this->createStack();

        $this->addRefreshMiddlewareToStack($stack);
        $this->addRetryMiddlewareToStack($stack);

        $defaultOptions = [
            'handler' => $stack,
            'base_uri' => $this->getBaseUri(),
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
            ],
        ];

        return new Client(array_merge($defaultOptions, $extraOptions));
    }

    protected function retryDecider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null
        ) {
            
            if ($retries >= self::MAX_RETRIES) {
                return false;
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
                            $requestWithNewToken = $request->withHeader(
                                'Authorization',
                                sprintf("Bearer %s", $this->getRefreshedToken())
                            );

                            return $this->getHttp()->send($requestWithNewToken, $options);
                        }
                        return $response;
                    }
                );
            };
        };
    }

    private function getRefreshedToken(): string
    {
        return $this->handleRefreshToken()['access_token'];
    }

    public function handleRefreshToken(): array
    {
        return $this->oAuth2->refreshToken($this->refreshToken);
    }

    private function createStack(): HandlerStack
    {
        return HandlerStack::create();
    }

    private function addRefreshMiddlewareToStack(HandlerStack $stack): void
    {
        $stack->push($this->middlewareRefreshToken());
    }

    private function addRetryMiddlewareToStack(HandlerStack $stack): void
    {
        $stack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
    }

    private function getBaseUri(): string
    {
        return sprintf(
            "%s/api/%s/",
            Endpoint::ENDPOINTS[$this->getEnvironment()],
            Endpoint::VERSIONS[$this->getEnvironment()]
        );
    }
}
