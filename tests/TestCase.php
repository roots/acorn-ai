<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Roots\AcornAi\AcornAiServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AcornAiServiceProvider::class];
    }
}
