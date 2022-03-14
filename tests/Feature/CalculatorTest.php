<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Feature;

require "vendor/autoload.php";

use Dotenv\Dotenv;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\Resources\Shipment\Product;
use MelhorEnvio\Resources\Resource;
use MelhorEnvio\MelhorEnvioSdkPhp\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    protected Shipment $shipment;

    protected Calculator $calculator;

    public function __construct()
    {
        parent::__construct();

        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $oAuth2 = new OAuth2(
            $_ENV['TEST_CLIENT_ID'],
            $_ENV['TEST_CLIENT_SECRET'],
            $_ENV['TEST_REDIRECT_URI']
        );

        $this->shipment = new Shipment(
            $oAuth2,
            $_ENV['ACCESS_TOKEN'],
            $_ENV['REFRESH_TOKEN']
        );

        $this->calculator = new Calculator($this->shipment);
    }

    /**
     * @test
     */
    public function shold_return_ok_when_call_method_calculate()
    {
        $this->calculator->postalCode('01010010', '20271130');
        $this->calculator->setOwnHand();
        $this->calculator->setReceipt(false);
        $this->calculator->setCollect(false);

        $this->calculator->addProducts(
            new Product(uniqid(), 40, 30, 50, 10.00, 100.0, 1),
            new Product(uniqid(), 5, 1, 10, 0.1, 50.0, 1)
        );

        $quotations =  $this->calculator->calculate();

        $this->assertTrue(is_array($quotations));
    }
}
