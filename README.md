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
    | This value defines the default namespace for your universal factories. You can
    | change it to fit your application's needs.
    |
    */
    'default_namespace' => 'App\\Factories\\',
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
    //
    // if this property is omitted, the package will check for a class with the same name,
    // (minus 'Factory') within the same namespace as the factory 
    //  
    // Ex. \BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfoFactory will look 
    // for a class \BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfo
    // 
    protected $class = UserInfo::class;

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
        // add global callbacks within the configure() method here
        // or add state specific callbacks within state methods
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

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nick Poulos](https://github.com/BeneathTheSurfaceLabs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
