<?php

namespace Tests;

require_once __DIR__ . "/../vendor/autoload.php";

use AspectMock\Test as AspectMock;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        AspectMock::clean();
    }
}