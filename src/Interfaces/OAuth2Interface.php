<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

interface OAuth2Interface
{
    public function refreshToken(string $refreshToken): array;

    // public function getClientId(): int;

    // public function getAppSecret(): string;

    // public function getEnvironment(): string;
}
