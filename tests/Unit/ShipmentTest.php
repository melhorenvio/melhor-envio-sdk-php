<?php

namespace Tests\Unit;

use Generator;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MelhorEnvio\Enums\Endpoint;
use MelhorEnvio\Exceptions\CalculatorException;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Package;
use Mockery;
use Mockery\MockInterface;
use Tests\Support\TestingShipment;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    private const ACCESS_TOKEN = '::access-token::';
    private const REFRESH_TOKEN = '::refresh-token::';
    private const VALID_ENVIRONMENT = 'production';

    /** @var OAuth2|MockInterface */
    private $oAuth2Mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oAuth2Mock = Mockery::mock(OAuth2::class, [
            'getEnvironment' => self::VALID_ENVIRONMENT,
        ]);
    }

    /**
     * @test
     * @small
     * @dataProvider retryProvider
     */
    public function retries_when_http_error_code_is_greater_or_equal_to_500(int $status, int $retryTimes): void
    {
        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $errorResponses = array_fill(0, $retryTimes, new Response($status, [], null));

        $shipment->fake(
            ...$errorResponses,
            ...[new Response(200, [], '{"foo": "bar"}')]
        );

        $sut = $this->calculateBasicShipment($shipment);

        $this->assertSame(['foo' => 'bar'], $sut);
    }

    /**
     * @test
     * @small
     */
    public function does_not_retry_when_http_error_code_is_less_than_500(): void
    {
        $expectedStatusCode = 400;
        $expectedMessage = '::message::';

        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $shipment->fake(new Response($expectedStatusCode, [], $expectedMessage));

        try {
            $this->calculateBasicShipment($shipment);
        } catch (CalculatorException $e) {
            $this->assertSame($expectedStatusCode, $e->getCode());
            $this->assertSame($expectedMessage, $e->getMessage());

            return;
        }

        $this->fail(sprintf('%s was not thrown.', CalculatorException::class));
    }

    /**
     * @test
     * @medium
     */
    public function retries_with_a_1_second_delay(): void
    {
        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $shipment->withDelay();

        $requestChronometer = [];

        $client = $shipment->makeClient([
            'on_stats' => function () use (&$requestChronometer) {
                $requestChronometer[] = microtime(true);
            }
        ]);
        $shipment->setHttp($client);

        $shipment->fake(
            new Response(500),
            new Response()
        );

        $this->calculateBasicShipment($shipment);

        $differenceBetweenRequests = $requestChronometer[1] - $requestChronometer[0];

        $this->assertSame(1, (int)$differenceBetweenRequests);
    }

    /**
     * @test
     * @small
     */
    public function reruns_the_request_with_a_refresh_token_when_a_401_error_occurs(): void
    {
        $refreshedAccessToken = '::refreshed-access-token::';

        $this->oAuth2Mock->allows([
            'refreshToken' => [
                'access_token' => $refreshedAccessToken,
            ],
        ]);

        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $shipment->fake(
            new Response(401),
            new Response(),
        );

        $this->calculateBasicShipment($shipment);

        /** @var Request $request */
        $request = $shipment->recorded()[1]['request'];

        $this->assertSame(
            sprintf("Bearer %s", $refreshedAccessToken),
            $request->getHeader('Authorization')[0]
        );
    }

    /**
     * @test
     * @small
     */
    public function has_10_seconds_timeout_for_requests(): void
    {
        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $shipment->fake(new Response());

        $this->calculateBasicShipment($shipment);

        $timeoutInSeconds = $shipment->recorded()[0]['options']['timeout'];

        $this->assertSame(10, $timeoutInSeconds);
    }

    /**
     * @test
     * @small
     * @dataProvider environmentProvider
     */
    public function sets_the_base_uri_based_on_the_current_environment(
        ?string $environment,
        string $expectedBaseUri
    ): void
    {
        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        if ($environment) {
            $shipment->setEnvironment($environment);
        }

        $shipment->fake(new Response());

        $this->calculateBasicShipment($shipment);

        $baseUri = (string)$shipment->recorded()[0]['options']['base_uri'];

        $this->assertSame($expectedBaseUri, $baseUri);
    }

    /**
     * @test
     * @small
     */
    public function sets_the_bearer_token_header_in_request(): void
    {
        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $shipment->fake(new Response());

        $this->calculateBasicShipment($shipment);

        /** @var Request $request */
        $request = $shipment->recorded()[0]['request'];

        $this->assertSame(
            sprintf("Bearer %s", self::ACCESS_TOKEN),
            $request->getHeader('Authorization')[0]
        );
    }

    /**
     * @test
     * @small
     */
    public function sets_the_accept_application_json_header_in_request(): void
    {
        $shipment = new TestingShipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $shipment->fake(new Response());

        $this->calculateBasicShipment($shipment);

        /** @var Request $request */
        $request = $shipment->recorded()[0]['request'];

        $this->assertSame('application/json', $request->getHeader('Accept')[0]);
    }

    /**
     * @param  Shipment  $shipment
     * @return mixed
     */
    private function calculateBasicShipment(Shipment $shipment)
    {
        $calculator = $shipment->calculator();

        $calculator->postalCode('00000000', '00000000');
        $calculator->addPackage(new Package(1, 1, 1, 1, 1));

        return $calculator->calculate();
    }

    public function retryProvider(): Generator
    {
        $statusCodes = [500, 501, 502];

        foreach ($statusCodes as $code) {
            yield "{$code} - retries once" => [$code, 1];
            yield "{$code} - retries twice" => [$code, 2];
        }
    }

    public function environmentProvider(): Generator
    {
        $url = Endpoint::ENDPOINTS['production'] . '/api/' . Endpoint::VERSIONS['production'] . '/';
        yield "[no environment specified] {$url}" => [
            null,
            $url
        ];

        foreach (Endpoint::ENDPOINTS as $environment => $url) {
            $url .= '/api/' . Endpoint::VERSIONS[$environment] . '/';
            yield "[{$environment}] {$url}" => [$environment, $url];
        }
    }
}
