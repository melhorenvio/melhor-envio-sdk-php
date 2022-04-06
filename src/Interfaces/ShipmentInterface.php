<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

use GuzzleHttp\Client;
use MelhorEnvio\MelhorEnvioSdkPhp\Calculator;
use Psr\Http\Message\RequestInterface;

interface ShipmentInterface
{
    public function client(): Client;

    protected function retryDecider();

    protected function retryDelay();

    protected function middlewareRefreshToken();

    private function updateToken(RequestInterface $request);

    public function handleRefreshToken(): array;
}
