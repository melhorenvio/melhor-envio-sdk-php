<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp;

use MelhorEnvio\MelhorEnvioSdkPhp\Interfaces\EventInterface;

class Event implements EventInterface
{
    private static $events = [];

    public static function listen($name, $callback)
    {
        self::$events[$name][] = $callback;
    }

    public static function trigger($name, $argument = null)
    {
        foreach (self::$events[$name] as $event => $callback) {
            if ($argument && is_array($argument)) {
                call_user_func_array($callback, $argument);
            } elseif ($argument && !is_array($argument)) {
                call_user_func($callback, $argument);
            } else {
                call_user_func($callback);
            }
        }
    }
}
