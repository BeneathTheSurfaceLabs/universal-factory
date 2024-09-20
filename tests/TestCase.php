<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests;

use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactoryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            UniversalFactoryServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {}
}
