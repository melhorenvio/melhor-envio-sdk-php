<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Unit;

require "vendor/autoload.php";

use Dotenv\Dotenv;
use MelhorEnvio\Enums\Environment;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use PHPUnit\Framework\TestCase;

class ShipmentTest extends TestCase
{
    protected Shipment $shipment;

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
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_refresh_token()
    {
        $this->assertTrue(method_exists($this->shipment, 'getRefreshToken'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_app_id()
    {
        $this->assertTrue(method_exists($this->shipment, 'getAppId'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_app_secret()
    {
        $this->assertTrue(method_exists($this->shipment, 'getAppSecret'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_app_redirect_uri()
    {
        $this->assertTrue(method_exists($this->shipment, 'getAppRedirectUri'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_calculator()
    {
        $this->assertTrue(method_exists($this->shipment, 'calculator'));
    }

    /**
     * @test
     */
    public function should_return_correct_refresh_token_when_get_refresh_token()
    {
        $this->assertEquals($_ENV['REFRESH_TOKEN'], $this->shipment->getRefreshToken());
    }

    /**
     * @test
     */
    public function should_return_correct_app_id_when_get_app_id()
    {
        $this->assertEquals($_ENV['TEST_CLIENT_ID'], $this->shipment->getAppId());
    }

    /**
     * @test
     */
    public function should_return_correct_app_secret_when_get_app_secret()
    {
        $this->assertEquals($_ENV['TEST_CLIENT_SECRET'], $this->shipment->getAppSecret());
    }

    /**
     * @test
     */
    public function should_return_correct_app_redirect_uri_when_get_app_redirect_uri()
    {
        $this->assertEquals($_ENV['TEST_REDIRECT_URI'], $this->shipment->getAppRedirectUri());
    }

    /**
     * @test
     */
    public function should_return_class_calculator_when_get_calculator()
    {
        $this->assertInstanceOf('MelhorEnvio\MelhorEnvioSdkPhp\Calculator', $this->shipment->calculator());
    }
}
