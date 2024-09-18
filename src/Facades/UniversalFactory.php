<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory
 */
class UniversalFactory extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory::class;
    }
}
