<?php

namespace BeneathTheSurfaceLabs\UniversalFactory;

use Faker\Generator;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @template TClass
 */
abstract class UniversalFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TClass>|null
     */
    protected $class;

    protected ?int $count = null;

    protected Collection $states;

    protected Collection $afterMaking;

    protected Collection $recycle;

    protected Faker $faker;

    /**
     * @var string
     */
    public static $namespace = 'App\\Factories\\';

    /**
     * The default class name resolver.
     *
     * @var callable(self): class-string<TClass>|null
     */
    protected static $classNameResolver;

    /**
     * The factory name resolver.
     *
     * @var callable|null
     */
    protected static $factoryNameResolver;

    /**
     * Create a new factory instance.
     *
     * @param  int|null  $count
     * @return void
     *
     * @throws BindingResolutionException
     */
    final public function __construct(
        $count = null,
        ?Collection $states = null,
        ?Collection $afterMaking = null,
        ?Collection $recycle = null
    ) {
        $this->count = $count;
        $this->states = $states ?? new Collection;
        $this->afterMaking = $afterMaking ?? new Collection;
        $this->recycle = $recycle ?? new Collection;
        $this->faker = $this->withFaker();
        self::$namespace = config('universal-factory.default_namespace', 'App\\Factories\\');
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>|callable
     */
    abstract public function definition(): array|callable;

    /**
     * Get a new factory instance for the given attributes.
     *
     * @param  (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed>  $attributes
     */
    public static function new($attributes = []): static
    {
        return (new static)->state($attributes)->configure();
    }

    /**
     * Configure the factory.
     */
    public function configure(): static
    {
        return $this;
    }

    /**
     * Get a new factory instance for the given number of models.
     */
    public static function times(int $count): static
    {
        return static::new()->count($count);
    }

    /**
     * Specify how many classes should be generated.
     */
    public function count(?int $count): static
    {
        return $this->newInstance(['count' => $count]);
    }

    /**
     * Add a new state transformation to the model definition.
     *
     * @param  (callable(array<string, mixed>, TClass|null): array<string, mixed>)|array<string, mixed>  $state
     */
    public function state($state): static
    {
        return $this->newInstance([
            'states' => $this->states->concat([
                is_callable($state) ? $state : function () use ($state) {
                    return $state;
                },
            ]),
        ]);
    }

    public function afterMaking(\Closure $callback): static
    {
        $this->afterMaking->push($callback);

        return $this;
    }

    /**
     * Call the "after making" callbacks for the given model instances.
     */
    protected function callAfterMaking(Collection $instances): void
    {
        $instances->each(function ($model) {
            $this->afterMaking->each(function ($callback) use ($model) {
                $callback($model);
            });
        });
    }

    /**
     * Create a collection of models.
     *
     * @param  (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed>  $attributes
     * @return \Illuminate\Support\Collection<int, TClass>|TClass
     *
     * @throws \ReflectionException
     */
    public function make($attributes = [])
    {
        if (! empty($attributes)) {
            return $this->state($attributes)->make([]);
        }

        if ($this->count === null) {
            return tap($this->makeInstance(), function ($instance) {
                $this->callAfterMaking(collect([$instance]));
            });
        }

        if ($this->count < 1) {
            return collect($this->newClass());
        }

        $instances = collect(range(1, $this->count))->map(fn () => $this->makeInstance());

        $this->callAfterMaking($instances);

        return $instances;
    }

    /**
     * Make an instance of the class with the given attributes.
     *
     * @return TClass
     *
     * @throws \ReflectionException
     */
    protected function makeInstance()
    {
        return tap($this->newClass($this->getExpandedAttributes()), function ($instance) {});
    }

    /**
     * Get a raw attributes array for the model.
     */
    protected function getExpandedAttributes(): array
    {
        return $this->expandAttributes($this->getRawAttributes());
    }

    /**
     * Get the raw attributes for the class as an array.
     */
    protected function getRawAttributes(): array
    {
        return $this->states->pipe(function ($states) {
            return $states;
        })->reduce(function ($carry, $state) {
            if ($state instanceof \Closure) {
                $state = $state->bindTo($this);
            }

            return array_merge($carry, $state($carry));
        }, $this->definition());
    }

    /**
     * Expand all attributes to their underlying values.
     */
    protected function expandAttributes(array $definition): array
    {
        return collect($definition)
            ->map(function ($attribute, $key) use (&$definition) {
                if (is_callable($attribute) && ! is_string($attribute) && ! is_array($attribute)) {
                    $attribute = $attribute($definition);
                }

                $definition[$key] = $attribute;

                return $attribute;
            })
            ->all();
    }

    /**
     * Create a new instance of the factory builder with the given mutated properties.
     */
    protected function newInstance(array $arguments = []): static
    {
        return new static(...array_values(array_merge([
            'count' => $this->count,
            'states' => $this->states,
            'afterMaking' => $this->afterMaking,
            'recycle' => $this->recycle,
        ], $arguments)));
    }

    /**
     * Get a new class instance with attributes mapped to constructor parameters.
     *
     * @param  array<string, mixed>  $attributes
     * @return TClass
     *
     * @throws \ReflectionException
     */
    public function newClass(array $attributes = [])
    {
        $class = $this->className();
        $constructor = (new \ReflectionClass($class))->getConstructor();

        return $constructor
            ? new $class(...$this->resolveParameters($constructor, $attributes))
            : new $class;
    }

    /**
     * Resolve parameters for the class constructor from the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<int, mixed>
     */
    protected function resolveParameters(\ReflectionMethod $constructor, array $attributes): array
    {

        return collect($constructor->getParameters())
            ->map(fn ($param) => $attributes[$param->getName()] ?? null)
            ->all();
    }

    /**
     * Get the name of the model that is generated by the factory.
     *
     * @return class-string<TClass>
     */
    public function className(): string
    {
        $resolver = function (self $factory) {
            $namespacedFactoryBasename = Str::replaceLast(
                'Factory', '', Str::replaceFirst(static::$namespace, '', get_class($factory))
            );

            $factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

            $appNamespace = static::appNamespace();

            return (class_exists($appNamespace.$namespacedFactoryBasename)) ?
                $appNamespace.$namespacedFactoryBasename :
                $appNamespace.$factoryBasename;
        };

        return $this->class ?? $resolver($this);
    }

    /**
     * Specify the callback that should be invoked to guess class names based on factory names.
     *
     * @param  callable(self): class-string<TClass>  $callback
     */
    public static function guessClassNamesUsing(callable $callback): void
    {
        static::$classNameResolver = $callback;
    }

    /**
     * Specify the default namespace that contains the application's universal factories.
     */
    public static function useNamespace(string $namespace): void
    {
        static::$namespace = $namespace;
    }

    /**
     * Get a new factory instance for the given model name.
     *
     * @param  class-string<TClass>  $className
     * @return \BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory<TClass>
     */
    public static function factoryForClass(string $className): UniversalFactory
    {
        $factory = static::resolveFactoryName($className);

        return $factory::new();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     *
     * @throws BindingResolutionException
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Get the factory name for the given class name.
     *
     * @param  class-string<TClass>  $className
     * @return class-string<UniversalFactory<TClass>>
     */
    public static function resolveFactoryName(string $className)
    {
        $resolver = static::$factoryNameResolver ?? function (string $className) {
            // Step 1: Use ReflectionClass to get the basename (without the namespace)
            $shortClassName = class_basename($className);

            // Step 2: Check if the factory exists in the configured namespace without 'Factory' suffix
            $factoryClass = static::$namespace . $shortClassName;
            if (class_exists($factoryClass)) {
                return $factoryClass;
            }

            // Step 3: Check if the factory exists in the configured namespace with 'Factory' appended
            $factoryClassWithSuffix = static::$namespace . $shortClassName . 'Factory';
            if (class_exists($factoryClassWithSuffix)) {
                return $factoryClassWithSuffix;
            }

            // Step 4: Fallback to the original class's namespace, appending 'Factory'
            $sameNamespaceFactory = $className . 'Factory';
            if (class_exists($sameNamespaceFactory)) {
                return $sameNamespaceFactory;
            }

            // If none of the above, return the class with 'Factory' appended in the configured namespace
            return static::$namespace . $shortClassName . 'Factory';
        };

        return $resolver($className);
    }


    /**
     * Get the application namespace for the application.
     *
     * @return string
     */
    protected static function appNamespace()
    {
        try {
            return Container::getInstance()
                ->make(Application::class)
                ->getNamespace();
        } catch (\Throwable) {
            return 'App\\';
        }
    }
}
