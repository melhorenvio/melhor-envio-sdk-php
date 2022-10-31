<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

use GuzzleHttp\Client;

interface ShipmentInterface
{
    public function client(): Client;

    public function handleRefreshToken(): array;
}
