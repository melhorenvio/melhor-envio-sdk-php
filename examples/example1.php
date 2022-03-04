<?php

require "./vendor/autoload.php";

use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\Resources\Shipment\Product;

$appData = [
    'client_id' => 2635,
    'client_secret' => 'O9WeVIi7zzCNhqveldS7oEm0YSF5lU6gCilnSkRj',
    'redirect_uri' => 'https://bridge-woocommerce.test/callback'
];

$provider = new OAuth2(
    $appData['client_id'],
    $appData['client_secret'],
    $appData['redirect_uri']
);

$code = getopt("c:");
if (!empty($code['c'])) {
    $tokens = $provider->getAccessToken($code['c'], 'token');
    var_dump($tokens);
    die;
}

$provider->setScopes('shipping-calculate');
var_dump($provider->getAuthorizationUrl());
die;
