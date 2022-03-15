<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Unit;

require "vendor/autoload.php";

use Dotenv\Dotenv;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use PHPUnit\Framework\TestCase;

class OAuth2Test extends TestCase
{
    protected OAuth2 $oAuth2;

    public function __construct()
    {
        parent::__construct();

        $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->oAuth2 = new OAuth2(
            $_ENV['TEST_CLIENT_ID'],
            $_ENV['TEST_CLIENT_SECRET'],
            $_ENV['TEST_REDIRECT_URI']
        );
    }

    /**
     * @test
     */
    public function return_ok_If_oauth_has_property_client()
    {
        $this->assertTrue(property_exists($this->oAuth2, 'client'));
    }

    /**
     * @test
     */
    public function return_ok_If_oauth_has_property_environment()
    {
        $this->assertTrue(property_exists($this->oAuth2, 'environment'));
    }

    /**
     * @test
     */
    public function return_ok_If_oauth_has_property_client_id()
    {
        $this->assertTrue(property_exists($this->oAuth2, 'clientId'));
    }

    /**
     * @test
     */
    public function return_ok_If_oauth_has_property_client_secret()
    {
        $this->assertTrue(property_exists($this->oAuth2, 'clientSecret'));
    }

    /**
     * @test
     */
    public function return_ok_If_oauth_has_property_redirect_uri()
    {
        $this->assertTrue(property_exists($this->oAuth2, 'redirectUri'));
    }

    /**
     * @test
     */
    public function return_ok_If_oauth_has_property_scope()
    {
        $this->assertTrue(property_exists($this->oAuth2, 'scope'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_refresh_token()
    {
        $this->assertTrue(method_exists($this->oAuth2, 'refreshToken'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_client_id()
    {
        $this->assertTrue(method_exists($this->oAuth2, 'getClientId'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_app_secret()
    {
        $this->assertTrue(method_exists($this->oAuth2, 'getAppSecret'));
    }

    /**
     * @test
     */
    public function returns_true_when_exists_method_get_environment()
    {
        $this->assertTrue(method_exists($this->oAuth2, 'getEnvironment'));
    }
}
