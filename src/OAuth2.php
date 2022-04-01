<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use GuzzleHttp\Client;
use MelhorEnvio\Auth\Exceptions\RefreshTokenException;
use MelhorEnvio\Auth\OAuth2 as AuthO2Auth;
use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\OAuth2Interface;

class OAuth2 extends AuthO2Auth implements OAuth2Interface
{
    protected Client $client;

    public function __construct(string  $clientId, string $clientSecret, string $redirectUri = null)
    {
        parent::__construct($clientId, $clientSecret, $redirectUri);
    }

    /**
     * @throws RefreshTokenException
     */
    public function refreshToken(string $refreshToken): array
    {
        $tokens  = parent::refreshToken($refreshToken);
        if (!empty($tokens['access_token']) && !empty($tokens['refresh_token'])) {
            Event::trigger('refresh', [$tokens['access_token'], $tokens['refresh_token']]);
        }
        return $tokens;
    }
}
