<?php

require "./vendor/autoload.php";

use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Product;

Event::listen('refresh', function (ACCESS_TOKEN, ACCESS_TOKEN): void {
    // Put here trading rule to save accessToken e refreshToken.
});

$oAuth2 = new OAuth2(
    CLIENT_ID,
    CLIENT_SECRET,
    REDIRECT_URI
);

$shipment = new Shipment(
    $oAuth2,
    ACCESS_TOKEN,
    ACCESS_TOKEN
);

$calculator = $shipment->calculator();

$calculator->postalCode('01010010', '20271130');

$calculator->setOwnHand();
$calculator->setReceipt(false);
$calculator->setCollect(false);

$calculator->addProducts(
    new Product(uniqid(), 40, 30, 50, 10.00, 100.0, 1),
    new Product(uniqid(), 5, 1, 10, 0.1, 50.0, 1)
);

$quotations = $calculator->calculate();

var_dump($quotations);
