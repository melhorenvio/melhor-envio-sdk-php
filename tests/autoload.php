<?php

use AspectMock\Kernel;

include __DIR__.'/../vendor/autoload.php';

$kernel = Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [__DIR__.'/../src'],
    'cacheDir'  => '/tmp',
]);