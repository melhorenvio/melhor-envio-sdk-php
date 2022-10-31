<?php

namespace Tests\Unit;

use Dotenv\Dotenv;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Calculator;
use Tests\TestCase;

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
