<?php

namespace Tests\Unit;

use MelhorEnvio\MelhorEnvioSdkPhp\Event;
use Tests\TestCase;

class EventTest extends TestCase
{
    /**
     * @test
     * @small
     */
    public function it_can_register_and_trigger_the_callback(): void
    {
        $callCount = 0;

        $callback = static function () use (&$callCount) {
            $callCount++;
        };

        Event::listen('foo', $callback);
        Event::trigger('foo');

        $this->assertSame(1, $callCount);
    }

    /**
     * @test
     * @small
     */
    public function it_can_register_and_trigger_multiple_callbacks_with_the_same_name(): void
    {
        $callback1CallCount = 0;
        $callback2CallCount = 0;

        $callback1 = static function () use (&$callback1CallCount) {
            $callback1CallCount++;
        };
        $callback2 = static function () use (&$callback2CallCount) {
            $callback2CallCount++;
        };

        Event::listen('foo', $callback1);
        Event::listen('foo', $callback2);

        Event::trigger('foo');

        $this->assertSame(1, $callback1CallCount);
        $this->assertSame(1, $callback2CallCount);
    }

    /**
     * @test
     * @small
     */
    public function it_can_pass_one_argument_to_the_callback(): void
    {
        $receivedArgs = null;
        $expectedArg = 'arg1';

        $callback = static function (...$args) use (&$receivedArgs) {
            $receivedArgs = $args;
        };

        Event::listen('foo', $callback);
        Event::trigger('foo', $expectedArg);

        $this->assertCount(1, $receivedArgs);
        $this->assertSame($expectedArg, $receivedArgs[0]);
    }

    /**
     * @test
     * @small
     */
    public function it_can_pass_multiple_arguments_to_the_callback(): void
    {
        $receivedArgs = null;
        $expectedArgs = [
            'arg1',
            'arg2',
        ];

        $callback = static function (...$args) use (&$receivedArgs) {
            $receivedArgs = $args;
        };

        Event::listen('foo', $callback);
        Event::trigger('foo', $expectedArgs);

        $this->assertSame($expectedArgs, $receivedArgs);
    }
}