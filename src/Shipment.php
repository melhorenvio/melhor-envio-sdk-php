<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use MelhorEnvio\Shipment as ShipmentSDK;
use MelhorEnvio\MelhorEnvioSdkPhp\Calculator;

class Shipment extends ShipmentSDK
{
    protected int $appId;

    protected string $appSecret;

    protected string $appRedirectUri;

    protected string $refreshToken;

    public function __construct(
        string $accessToken,
        string $refreshToken,
        string $environment,
        int $appId,
        string $appSecret,
        string $appRedirectUri
    ) {
        parent::__construct($accessToken, $environment);

        $this->appId = $appId;

        $this->appSecret = $appSecret;

        $this->appRedirectUri = $appRedirectUri;

        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getAppId(): int
    {
        return $this->appId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function getAppRedirectUri(): string
    {
        return $this->appRedirectUri;
    }

    public function calculator(): Calculator
    {
        $calculator =  new Calculator($this);

        return $calculator;
    }
}
