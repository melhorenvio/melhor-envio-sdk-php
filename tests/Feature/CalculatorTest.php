<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Feature;

require "vendor/autoload.php";

use Dotenv\Dotenv;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
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

        $appData = [
            'client_id' => $_ENV['TEST_CLIENT_ID'],
            'client_secret' => $_ENV['TEST_CLIENT_SECRET'],
            'redirect_uri' => $_ENV['TEST_REDIRECT_URI']
        ];

        $this->shipment = new Shipment(
            $_ENV['ACCESS_TOKEN'],
            $_ENV['REFRESH_TOKEN'],
            Environment::SANDBOX,
            $appData['client_id'],
            $appData['client_secret'],
            $appData['redirect_uri']
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

        $this->assertOk(is_array($quotations));
    }
}
