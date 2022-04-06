<?php

namespace MelhorEnvio\MelhorEnvioSdkPhp\Interfaces;

interface OAuth2Interface
{
    public function refreshToken(string $refreshToken): array;
}
