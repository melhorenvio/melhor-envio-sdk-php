<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use JsonException;
use MelhorEnvio\Auth\Exceptions\RefreshTokenException;
use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use MelhorEnvio\MelhorEnvioSdkPhp\OAuth2;
use Mockery;
use Tests\TestCase;

class OAuth2Test extends TestCase
{
    /**
     * @test
     * @small
     * @throws RefreshTokenException
     * @throws JsonException
     */
    public function it_calls_the_registered_refresh_event_when_the_refresh_token_is_issued(): void
    {
        $expectedAccessToken = '::access_token::';
        $expectedRefreshToken = '::refresh_token::';

        $oAuth2 = new OAuth2(
            '::client-id::',
            '::client-secret::',
            '::redirect-uri::',
        );

        $this->mockOAuth2RefreshTokenReturn($oAuth2, [
            'access_token' => $expectedAccessToken,
            'refresh_token' => $expectedRefreshToken,
        ]);

        $receivedArgs = null;
        Event::listen('refresh', static function (...$args) use (&$receivedArgs) {
            $receivedArgs = $args;
        });

        $oAuth2->refreshToken('::refresh-token::');

        $this->assertCount(2, $receivedArgs);
        $this->assertEquals($expectedAccessToken, $receivedArgs[0]);
        $this->assertEquals($expectedRefreshToken, $receivedArgs[1]);
    }

    /**
     * @throws JsonException
     */
    private function mockOAuth2RefreshTokenReturn(OAuth2 $oAuth2, array $return): void
    {
        $oAuth2->setClient(
            Mockery::mock(Client::class, [
                'post->getBody' => json_encode($return, JSON_THROW_ON_ERROR)
            ])
        );
    }
}
