# Universal Factories

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beneaththesurfacelabs/universal-factory.svg?style=flat-square)](https://packagist.org/packages/beneaththesurfacelabs/universal-factory)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/beneaththesurfacelabs/universal-factory/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/beneaththesurfacelabs/universal-factory/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/beneaththesurfacelabs/universal-factory/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/beneaththesurfacelabs/universal-factory/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/beneaththesurfacelabs/universal-factory.svg?style=flat-square)](https://packagist.org/packages/beneaththesurfacelabs/universal-factory)

<p align="center"><a href="https://gridz.football" target="_blank"><img src="https://github.com/nickpoulos/gridz.football/blob/main/resources/img/logo.png?raw=true" width="400" alt="Gridz Logo"></a></p>
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
  - States
  - Callbacks
  - Integration With Faker

### Example Classes and their Universal Factories
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
    
    // If the below method is omitted, the package will look for a class named
    // UserInfoFactory within the same namespace (BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples)
    public static function newFactory(): UserInfoFactory
    {
        return UserInfoFactory::new();
    }
}


```

- Create your factory using the included Artisan command:

`php artisan make:universal-factory UserInfoFactory --class=App\MyClass\TestData --namespace=App\MyClass\`

It will create a file similar to the one below, within the `default_namespace` from our config file. 

If you do not provide the factory name or classname, the package will attempt to locate it for you based on a few different naming conventions. You can always choose to override these naming conventions and provide your own class names. 

```php
<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;

class UserInfoFactory extends UniversalFactory
{
    // If you choose to omit this property, the package will look
    // for a corresponding UserInfo class within the same namespace
    // as your factory
    protected $class = UserInfo::class;

    /**
     * Define the class's default attributes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'birthday' => $this->faker->dateTime,
            'age' => $this->faker->numberBetween(21, 40),
        ];
    }

    public function age(int $age): self
    {
        return $this->afterMaking(fn (UserInfo $exampleClass) => $exampleClass->age = $age);
    }
    
    // you can use afterMaking() within state methods for more direct control
    public function unrestrictedAge(): self
    {
        return $this->afterMaking(function (UserInfo $exampleClass) {
            $birthday = fake()->dateTimeBetween('now', '-21 years');
            $exampleClass->birthday = $birthday;
            $exampleClass->age = (new \DateTime)->diff($birthday)->y;
        });
    }
    
    // a more typical way to use state methods
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


## Contact me

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/universal-factory.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/universal-factory)

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Credits

- [Nick Poulos](https://github.com/BeneathTheSurfaceLabs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
