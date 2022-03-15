<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

interface EventInterface
{
    public static function listen($name, $callback);

    public static function trigger($name, $argument = null);
}
