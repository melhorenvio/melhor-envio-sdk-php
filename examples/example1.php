<?php

require "./vendor/autoload.php";

use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;

$provider = new OAuth2(
    CLIENT_ID,
    CLIENT_SECRET,
    REDIRECT_URI
);

$code = getopt("c:");
if (!empty($code['c'])) {
    $tokens = $provider->getAccessToken($code['c'], 'token');
    var_dump($tokens);
    die;
}

$provider->setScopes('shipping-calculate');
var_dump($provider->getAuthorizationUrl());
