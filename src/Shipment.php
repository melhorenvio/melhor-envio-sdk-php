<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use MelhorEnvio\Shipment as ShipmentSDK;
use MelhorEnvio\MelhorEnvioSdkPhp\Calculator;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\ShipmentInterface;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;

class Shipment extends ShipmentSDK implements ShipmentInterface
{
    public OAuth2 $oAuth2;

    protected String $refreshToken;

    public function __construct(OAuth2 $oAuth2, string $accessToken, String $refreshToken)
    {
        parent::__construct($accessToken, $oAuth2->getEnvironment());

        $this->oAuth2 = $oAuth2;

        $this->refreshToken = $refreshToken;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
