<?php

namespace BeneathTheSurfaceLabs\UniversalFactory;

use BeneathTheSurfaceLabs\UniversalFactory\Enum\ClassConstructionStrategy;
use Faker\Generator;
use Faker\Generator as Faker;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    protected ClassConstructionStrategy $classConstructionStrategy = ClassConstructionStrategy::CONTAINER_BASED;

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
        $instances->each(function ($class) {
            $this->afterMaking->each(function ($callback) use ($class) {
                $callback($class);
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
            return $this->state($attributes)->make();
        }

        if ($this->count === null || $this->count < 1) {
            return tap($this->makeInstance(), function ($instance) {
                $this->callAfterMaking(collect([$instance]));
            });
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
        return $this->newClass($this->getExpandedAttributes());
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
        return $this->states->reduce(function ($carry, $state) {

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
                if ($attribute instanceof UniversalFactory) {
                    $attribute = $attribute->make();
                }

                if (
                    is_callable($attribute) &&
                    ! is_string($attribute) &&
                    ! is_array($attribute)  &&
                    ! enum_exists(is_object($attribute) ? get_class($attribute) : '')
                ) {
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
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function newClass(array $attributes = [])
    {
        $class = $this->className();

        return match ($this->classConstructionStrategy) {
            ClassConstructionStrategy::ARRAY_BASED => new $class($attributes),
            ClassConstructionStrategy::CONTAINER_BASED => app()->makeWith($class, $attributes),
            ClassConstructionStrategy::REFLECTION_BASED => new $class(...$this->resolveClassParameters($class, $attributes)),
        };
    }

    /**
     * Resolve parameters for the class constructor from the given attributes.
     *
     * @param  class-string<TClass>  $class
     * @param  array<string, mixed>  $attributes
     * @return array<int, mixed>
     *
     * @throws \ReflectionException
     */
    protected function resolveClassParameters(string $class, array $attributes): array
    {
        $constructor = (new \ReflectionClass($class))->getConstructor();

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
        $resolver = static::$classNameResolver ?? function (self $factory) {
            $namespacedFactoryBasename = Str::replaceLast(
                'Factory', '', Str::replaceFirst(static::$namespace, '', get_class($factory))
            );

            $factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

            $appNamespace = static::appNamespace();

            return match (true) {
                class_exists($namespacedFactoryBasename) => $namespacedFactoryBasename,
                class_exists($appNamespace.$factoryBasename) => $appNamespace.$factoryBasename,
                default => throw new \Exception('Cannot locate base class for your factory!')
            };
        };

        return $this->class ?? $resolver($this);
    }

    /**
     * Specify the callback that should be invoked to guess class names based on factory names.
     *
     * @param  null|callable(self): class-string<TClass>  $callback
     */
    public static function guessClassNamesUsing(?callable $callback): void
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

            $factoryClass = static::$namespace.$shortClassName;
            if (class_exists($factoryClass)) {
                return $factoryClass;
            }

            $factoryClassWithSuffix = static::$namespace.$shortClassName.'Factory';
            if (class_exists($factoryClassWithSuffix)) {
                return $factoryClassWithSuffix;
            }

            $sameNamespaceFactory = $className.'Factory';
            if (class_exists($sameNamespaceFactory)) {
                return $sameNamespaceFactory;
            }

            return static::$namespace.$shortClassName.'Factory';
        };

        return $resolver($className);
    }

    public function useConstructionStrategy(ClassConstructionStrategy $strategy): static
    {
        $this->classConstructionStrategy = $strategy;

        return $this;
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
