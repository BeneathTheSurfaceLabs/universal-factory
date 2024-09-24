<?php

namespace BeneathTheSurfaceLabs\UniversalFactory;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use BeneathTheSurfaceLabs\UniversalFactory\Commands\UniversalFactoryCommand;

class UniversalFactoryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('universal-factory')
            ->hasConfigFile()
            ->hasCommand(UniversalFactoryCommand::class);
    }
}
