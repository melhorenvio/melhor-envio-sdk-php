<?php

namespace Tests\Unit;

use AspectMock\Test as AspectMock;
use Generator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MelhorEnvio\Enums\Endpoint;
use MelhorEnvio\Exceptions\CalculatorException;
use MelhorEnvio\Exceptions\InvalidCalculatorPayloadException;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use MelhorEnvio\MelhorEnvioSdkPhp\Shipment;
use MelhorEnvio\Resources\Shipment\Package;
use Mockery;
use Mockery\MockInterface;
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
     * @throws InvalidCalculatorPayloadException|CalculatorException
     */
    public function retries_when_http_error_code_is_greater_or_equal_to_500(int $status, int $retryTimes): void
    {
        $this->disableRetryDelay();

        $errorResponses = array_fill(0, $retryTimes, new Response($status, [], null));

        $this->mockResponses([
            ...$errorResponses,
            new Response(200, [], '{"foo": "bar"}')
        ]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $sut = $this->calculateBasicShipment($shipment);

        $this->assertSame(['foo' => 'bar'], $sut);
    }

    /**
     * @test
     * @small
     * @throws InvalidCalculatorPayloadException
     */
    public function does_not_retry_when_http_error_code_is_less_than_500(): void
    {
        $expectedStatusCode = 400;
        $expectedMessage = '::message::';

        $this->mockResponses([
            new Response($expectedStatusCode, [], $expectedMessage)
        ]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

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
        $this->mockResponses([new Response(500), new Response()]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $requestChronometer = [];

        $client = $shipment->makeClient([
            'on_stats' => function () use (&$requestChronometer) {
                $requestChronometer[] = microtime(true);
            }
        ]);
        $shipment->setHttp($client);

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

        $history = &$this->mockResponses([
            new Response(401),
            new Response(),
        ]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $this->calculateBasicShipment($shipment);

        // The first request that fails is overrided by the successfly
        // request due to the way the code is dealing with the retries.
        /** @var Request $request */
        $request = $history[0]['request'];

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
        $history = &$this->mockResponses([new Response()]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $this->calculateBasicShipment($shipment);

        $timeoutInSeconds = $history[0]['options']['timeout'];

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
    ): void {
        $history = &$this->mockResponses([new Response()]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        if ($environment) {
            $shipment->setEnvironment($environment);
        }

        $this->calculateBasicShipment($shipment);

        $baseUri = (string)$history[0]['options']['base_uri'];

        $this->assertSame($expectedBaseUri, $baseUri);
    }

    /**
     * @test
     * @small
     */
    public function sets_the_bearer_token_header_in_request(): void
    {
        $history = &$this->mockResponses([new Response()]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $this->calculateBasicShipment($shipment);

        /** @var Request $request */
        $request = $history[0]['request'];

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
        $history = &$this->mockResponses([new Response()]);

        $shipment = new Shipment(
            $this->oAuth2Mock,
            self::ACCESS_TOKEN,
            self::REFRESH_TOKEN
        );

        $this->calculateBasicShipment($shipment);

        /** @var Request $request */
        $request = $history[0]['request'];

        $this->assertSame('application/json', $request->getHeader('Accept')[0]);
    }

    private function disableRetryDelay(): void
    {
        AspectMock::double(Shipment::class, ['retryDelay' => fn() => static fn() => 0]);
    }

    private function &mockResponses(array $responses): array
    {
        $mock = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        AspectMock::double(Shipment::class, ['createStack' => $handlerStack]);

        return $container;
    }

    /**
     * @param  Shipment  $shipment
     * @return mixed
     * @throws CalculatorException
     * @throws InvalidCalculatorPayloadException
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
