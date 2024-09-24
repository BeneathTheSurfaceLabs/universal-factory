<?php

namespace BeneathTheSurfaceLabs\UniversalFactory;

use BeneathTheSurfaceLabs\UniversalFactory\Commands\UniversalFactoryCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
