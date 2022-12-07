<?php

namespace Tests\Support;

use Closure;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;

class TestingShipment extends Shipment
{
    private MockHandler $mockHandler;
    private array $recorded = [];
    private bool $shouldDelay = false;

    public function __construct(OAuth2 $oAuth2, string $accessToken, string $refreshToken)
    {
        $this->mockHandler = new MockHandler();

        parent::__construct($oAuth2, $accessToken, $refreshToken);
    }

    /**
     * @throws Exception
     */
    final protected function createStack(): HandlerStack
    {
        $handlerStack = parent::createStack();

        $handlerStack->setHandler($this->mockHandler);

        $history = Middleware::history($this->recorded);
        $handlerStack->push($history);

        return $handlerStack;
    }

    /**
     * Add fake client responses.
     *
     * @param  Response  ...$responses
     * @return void
     */
    final public function fake(Response ...$responses): void
    {
        $this->mockHandler->append(...$responses);
    }

    /**
     * @return array A history of the requests that were sent by a client.
     */
    final public function recorded(): array
    {
        return array_reverse($this->recorded);
    }

    /**
     * Enable delay between requests. The delay is disabled by default.
     *
     * @return void
     */
    final public function withDelay(): void
    {
        $this->shouldDelay = true;
    }

    final protected function retryDelay(): Closure
    {
        if ($this->shouldDelay) {
            return parent::retryDelay();
        }

        return static fn() => 0;
    }
}
