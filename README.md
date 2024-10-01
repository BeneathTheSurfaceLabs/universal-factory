# Universal Factories

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beneaththesurfacelabs/universal-factory.svg?style=flat-square)](https://packagist.org/packages/beneaththesurfacelabs/universal-factory)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/beneaththesurfacelabs/universal-factory/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/beneaththesurfacelabs/universal-factory/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/beneaththesurfacelabs/universal-factory/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/beneaththesurfacelabs/universal-factory/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/beneaththesurfacelabs/universal-factory.svg?style=flat-square)](https://packagist.org/packages/beneaththesurfacelabs/universal-factory)

<p align="center">
<img src="https://github.com/user-attachments/assets/171406c4-db0a-473c-850b-05538d92474a" width="50%">
</p>

Create Laravel-style Factory classes to quickly generate test data within your applications

## Why?

Laravel's existing factory implementation is truly amazing, but has become increasingly coupled to Eloquent models over the years. 

Prior to these changes, it was possible to use Laravel Factories for many different kinds of data, including things like DTOs, FormRequests, etc. 

In order to restore this ability, we can use this package to complement Laravel's existing Eloquent Factories.

## Installation

You can install the package via composer:

```bash
composer require beneaththesurfacelabs/universal-factory
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="universal-factory-config"
```

This is the contents of the published config file:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Namespace for Universal Factories
    |--------------------------------------------------------------------------
    |
    | This value defines the default namespace for the universal factories. You can
    | change it to fit your application's needs.
    |
    */
    'default_namespace' => 'App\\Factories\\',

    /*
    |--------------------------------------------------------------------------
    | Universal Factory Method Name
    |--------------------------------------------------------------------------
    |
    | This value allows the user to specify the name of the factory method
    | provided by the HasUniversalFactory trait.

    | For example, if your source class does too much, and already has a poorly
    | designed static factory() method that we cannot just override.
    |
    */
    'method_name' => 'factory',
];


```

## Usage

These Universal Factories are API-compatible with most features found within Laravel's Eloquent Factories. They are used in an identical fashion.

To use them, perform the following steps:

- Add the HasUniversalFactory trait to your class.
- Create your factory class using the included Artisan command, or by hand
- Use the same features you know and love from Laravel's Eloquent Factories
  - Factory States
  - Callbacks such as afterMaking
  - Nested Factory Definitions
  - Integration With Faker

### Example Classes and their Universal Factories

Example Class UserInfo:

```php
<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\Traits\HasUniversalFactory;

class UserInfo
{
    use HasUniversalFactory;

    public function __construct(
        public string $externalId,
        public string $name,
        public string $email,
        public \DateTime $birthday,
        public int $age,
        public ProfileData $profileData,
    ) {}
    
    // If the below method is omitted, the package will look for a class named UserInfoFactory
    // within the same namespace as this class -- BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples
    public static function newFactory(): UserInfoFactory
    {
        return UserInfoFactory::new();
    }
}


```

Example Factory Class UserInfoFactory: 

```php
<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;

class UserInfoFactory extends UniversalFactory
{
    /* 
       If the 'class' property is omitted, the package will check for a class with the same name
       (minus 'Factory'), within the same namespace as the factory 
       
       In this example, if omitted, the package would look for: 
       
       \BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfo
     */ 
    protected ?string $class = UserInfo::class;

    /**
     * Define the class's default attributes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            
            'externalId' => fn () => substr(str_replace(['+', '.', 'E'], '', microtime(true)), -10), // functional attribute definitions
            'name' => $this->faker->name, // typical faker usage
            'email' => $this->faker->email,
            'birthday' => $this->faker->dateTime,
            'age' => $this->faker->numberBetween(21, 40),
            'profileData' => ProfileData::factory(), // nested factory within definitions
        ];
    }

    public function configure(): static
    {
        /* 
            Add global callbacks within the configure() method here,
            or add state specific callbacks within state methods.
        
            Ex. unrestrictedAge() and restrictedAge() below
        */
        
        // This callback would happen anytime this factory was to generate a class
        $this->afterMaking(fn (UserInfo $userInfo) => $userInfo->profileData = ProfileData::factory()->withProfileFor($userInfo)->make());

        return $this;
    }

    public function unrestrictedAge(): self
    {
        // create state-specific methods
        return $this->state(function (array $attributes) {
            $birthday = fake()->dateTimeBetween('now', '-21 years');
            $attributes['birthday'] = $birthday;
            $attributes['age'] = (new \DateTime)->diff($birthday)->y;

            return $attributes;
        });
    }

    public function restrictedAge(): self
    {
        return $this->state(function (array $attributes) {
            $birthday = fake()->dateTimeBetween('-12 years', 'now');
            $attributes['birthday'] = $birthday;
            $attributes['age'] = (new \DateTime)->diff($birthday)->y;

            return $attributes;
        });
    }
}

```

## Class Construction
This package supports a few common strategies to instruct your factory how to construct your classes. Developers can easily override these with their own class construction implementation.

By default, this package will use the `ClassConstructionStrategy::CONTAINER_BASED` strategy, which takes advantage of the Laravel container to attempt to construct your class.  

Another, `ClassConstructionStrategy::REFLECTION_BASED`, uses PHP's Reflection classes to examine your class, and directly set the parameters it is able to inspect. 

The last strategy, `ClassConstructionStrategy::ARRAY_BASED`, assumes your class constructor takes an array of parameters, which will map to your class properties. This is similar to how Eloquent models are constructed.

Of course, if your class requires something more custom or complex to be constructed, you can easily override the newClass() method within your factory class.

Example Class ProfileData (Notice the constructor)

```php
<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\Traits\HasUniversalFactory;

class ProfileData
{
    use HasUniversalFactory;

    public ?string $facebookProfileUrl;
    public ?string $facebookAvatarUrl;
    public ?string $twitterProfileUrl;
    public ?string $twitterAvatarUrl;
    public ?string $gitHubProfileUrl;
    public ?string $githubAvatarUrl;
    public ?string $personalUrl;

    public function __construct(array $profileData)
    {
        $this->facebookProfileUrl = $profileData['facebookProfileUrl'] ?? null;
        $this->facebookAvatarUrl = $profileData['facebookAvatarUrl'] ?? null;
        $this->twitterProfileUrl = $profileData['twitterProfileUrl'] ?? null;
        $this->twitterAvatarUrl = $profileData['twitterAvatarUrl'] ?? null;
        $this->gitHubProfileUrl = $profileData['gitHubProfileUrl'] ?? null;
        $this->githubAvatarUrl = $profileData['githubAvatarUrl'] ?? null;
        $this->personalUrl = $profileData['personalUrl'] ?? null;
    }
}

```

Example Factory Class ProfileDataFactory (Sets Array Based Construction)

```php
<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use Illuminate\Support\Str;
use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;
use BeneathTheSurfaceLabs\UniversalFactory\Enum\ClassConstructionStrategy;

class ProfileDataFactory extends UniversalFactory
{
    protected ClassConstructionStrategy $classConstructionStrategy = ClassConstructionStrategy::ARRAY_BASED;

    public function definition(): array
    {
        return [
            'facebookProfileUrl' => fake()->url(),
            'facebookAvatarUrl' => fake()->imageUrl(),
            'twitterProfileUrl' => fake()->url(),
            'twitterAvatarUrl' => fake()->imageUrl(),
            'gitHubProfileUrl' => fake()->url(),
            'githubAvatarUrl' => fake()->imageUrl(),
            'personalUrl' => fake()->url(),
        ];
    }

    public function withProfileFor(UserInfo $userInfo): self
    {
        $usernameGenerator = function (UserInfo $userInfo) {
            $method = fake()->boolean() ? 'slug' : 'studly';

            return Str::$method(
                $userInfo->name,
                fake()->randomElement(['-', '_', '.']).
                (fake()->boolean() ? $userInfo->birthday->format(fake()->randomElement(['Y', 'y', 'my'])) : ''),
            );
        };

        $urls = [
            'facebook' => 'https://facebook.com/'.$usernameGenerator($userInfo),
            'twitter' => 'https://x.com/'.$usernameGenerator($userInfo),
            'github' => 'https://github.com/'.$usernameGenerator($userInfo),
            'personal' => 'https://'.Str::slug($userInfo->name).'.com/',
        ];

        return $this->state(function (array $attributes) use ($urls) {
            $attributes['facebookProfileUrl'] = $urls['facebook'];
            $attributes['twitterProfileUrl'] = $urls['twitter'];
            $attributes['gitHubProfileUrl'] = $urls['github'];
            $attributes['personalUrl'] = $urls['personal'];

            return $attributes;
        });
    }
}
```

Example newClass() Override

```php
public function newClass(array $attributes = [])
{
    return MyCustomClass::fromUserId($attributes['user_id']);
}
```

## Testing

To run the test suite, run the following: 
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Issues & Security Vulnerabilities

Please submit any issues (installation, usage, security, etc.) using the [GitHub Issues](https://github.com/BeneathTheSurfaceLabs/universal-factory/issues) tab above.

## Credits

- [Nick Poulos](https://github.com/nickpoulos)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
