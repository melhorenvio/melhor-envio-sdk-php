<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

interface CalculatorInterface
{
    public function calculate();

    public function updateResourceHttp(): void;

    public function handleRefreshToken(): array;
}
