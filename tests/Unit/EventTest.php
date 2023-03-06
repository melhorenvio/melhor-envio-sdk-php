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
    public function triggers_a_registered_callback(): void
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
    public function triggers_multiple_callbacks_registered_to_the_same_event(): void
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
    public function does_not_trigger_registered_callbacks_to_other_events(): void
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
        Event::listen('bar', $callback2);

        Event::trigger('foo');

        $this->assertSame(1, $callback1CallCount);
        $this->assertSame(0, $callback2CallCount);
    }

    /**
     * @test
     * @small
     */
    public function passes_one_argument_to_the_callback(): void
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
    public function passes_multiple_arguments_to_the_callback(): void
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