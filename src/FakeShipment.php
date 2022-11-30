<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use Closure;
use GuzzleHttp\HandlerStack;

class FakeShipment extends Shipment
{
    public static bool $shouldDelay = true;
    public static HandlerStack $handlerStack;

    protected function createStack(): HandlerStack
    {
        return self::$handlerStack ?? parent::createStack();
    }

    protected function retryDelay(): Closure
    {
        if (self::$shouldDelay) {
            return parent::retryDelay();
        }

        return static fn() => 0;
    }
}
