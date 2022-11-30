<?php

namespace Tests\Support;

use Closure;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;

class TestingShipment extends Shipment
{
    private HandlerStack $handlerStack;
    private array $recorded;
    private bool $shouldDelay = false;

    /**
     * @throws Exception
     */
    final protected function createStack(): HandlerStack
    {
        return $this->handlerStack = parent::createStack();
    }

    /**
     * Add fake client responses.
     *
     * @param  Response  ...$responses
     * @return void
     */
    final public function fake(Response ...$responses): void
    {
        $mock = new MockHandler($responses);

        $this->handlerStack->setHandler($mock);

        $this->recorded = [];
        $history = Middleware::history($this->recorded);
        $this->handlerStack->push($history);
    }

    /**
     * @return array A history of the requests that were sent by a client.
     */
    final public function recorded(): array
    {
        return $this->recorded;
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
