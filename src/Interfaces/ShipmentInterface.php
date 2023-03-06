<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

use GuzzleHttp\Client;

interface ShipmentInterface
{
    public function makeClient(): Client;

    public function handleRefreshToken(): array;
}
