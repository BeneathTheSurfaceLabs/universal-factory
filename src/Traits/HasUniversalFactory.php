<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Traits;

use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;

/**
 * @template TUniversalFactory of \BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;
 */
trait HasUniversalFactory
{
    /**
     * Dynamically provide the factory method for the model based on config.
     */
    public static function __callStatic($method, $arguments)
    {
        $factoryMethodName = config('universal-factory.method_name', 'factory');

        // Check if the called method is the factory method
        if ($method === $factoryMethodName) {
            return self::getUniversalFactory(...$arguments);
        }

        // If method doesn't match, continue as normal
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Get a new factory instance for the model.
     *
     * @param  (callable(array<string, mixed>, static|null): array<string, mixed>)|array<string, mixed>|int|null  $count
     * @param  (callable(array<string, mixed>, static|null): array<string, mixed>)|array<string, mixed>  $state
     * @return TUniversalFactory
     */
    protected static function getUniversalFactory($count = null, $state = [])
    {
        // Get factory for this class using the base factory logic
        $factory = static::newFactory() ?? UniversalFactory::factoryForClass(get_called_class());

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return TUniversalFactory|null
     */
    protected static function newFactory()
    {
        if (isset(static::$factory)) {
            return static::$factory::new();
        }

        return null;
    }
}
