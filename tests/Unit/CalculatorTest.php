<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Unit;

require "vendor/autoload.php";

use Dotenv\Dotenv;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
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

    /** @test */
    public function returns_true_when_exists_method_calculate()
    {
        $this->assertTrue(method_exists($this->calculator, 'calculate'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_update_resource_http()
    {
        $this->assertTrue(method_exists($this->calculator, 'updateResourceHttp'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_update_handle_refresh_token()
    {
        $this->assertTrue(method_exists($this->calculator, 'handleRefreshToken'));
    }
}
