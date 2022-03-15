<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use MelhorEnvio\Shipment as ShipmentSDK;
use MelhorEnvio\MelhorEnvioSdkPhp\Calculator;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\ShipmentInterface;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;

class Shipment extends ShipmentSDK implements ShipmentInterface
{
    protected int $appId;

    protected string $appSecret;

    protected string $appRedirectUri;

    protected string $refreshToken;

    public function __construct(OAuth2 $oAuth2, string $accessToken, string $refreshToken)
    {
        parent::__construct($accessToken, $oAuth2->getEnvironment());
        
        $this->appId = $oAuth2->getClientId();

        $this->appSecret = $oAuth2->getAppSecret();

        $this->appRedirectUri = $oAuth2->getRedirectUri();

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
