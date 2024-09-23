<?php

use BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\ProfileData;
use BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\ProfileDataFactory;
use BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfo;
use BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfoFactory;
use Illuminate\Support\Str;

it('Can Create A New Factory Instance', function () {
    $factory = UserInfo::factory();
    expect($factory)->toBeInstanceOf(UserInfoFactory::class);
});

it('Can Make A New Class With Empty State', function () {
    $factory = UserInfo::factory();
    $result = $factory->make();
    expect($result->age)->toBeBetween(21, 40);
    expect($result->name)->toBeString();
    expect(Str::of($result->email)->contains('@'))->toBeTrue();
    expect($result->birthday)->toBeInstanceOf(\DateTime::class);
    expect($result->profileData)->toBeInstanceOf(ProfileData::class);
    expect($result->profileData->facebookProfileUrl)->toContain('https://facebook.com/');
    expect($result->profileData->twitterProfileUrl)->toContain('https://x.com/');
    expect($result->profileData->gitHubProfileUrl)->toContain('https://github.com/');

});

it('Can Make Many New Classes With Empty State', function () {
    $factory = UserInfo::factory();
    $count = 5;
    $result = $factory->count($count)->make();
    expect($result->count())->toEqual($count);

    $result->each(function (UserInfo $result) {
        expect($result->age)->toBeBetween(21, 40);
        expect($result->name)->toBeString();
        expect(Str::of($result->email)->contains('@'))->toBeTrue();
        expect($result->birthday)->toBeInstanceOf(\DateTime::class);
    });
});

it('Can Make A New Class With State Overrides via factory()', function () {
    $factory = UserInfo::factory(['name' => 'Eric Cartman', 'email' => 'eric@southparkcows.com']);
    $result = $factory->make();

    expect($result->age)->toBeBetween(21, 40);
    expect($result->name)->toEqual('Eric Cartman');
    expect($result->email)->toEqual('eric@southparkcows.com');
    expect($result->birthday)->toBeInstanceOf(\DateTime::class);
});

it('Can Make A New Class With State Overrides via make()', function () {
    $factory = UserInfo::factory();
    $result = $factory->make(['name' => 'Eric Cartman', 'email' => 'eric@southparkcows.com']);

    expect($result->age)->toBeBetween(21, 40);
    expect($result->name)->toEqual('Eric Cartman');
    expect($result->email)->toEqual('eric@southparkcows.com');
    expect($result->birthday)->toBeInstanceOf(\DateTime::class);
});

it('Can Make A Many New Classes With State Overrides via factory()', function () {
    $factory = UserInfo::factory(['name' => 'Eric Cartman', 'email' => 'eric@southparkcows.com']);
    $count = 5;
    $result = $factory->count($count)->make();
    expect($result->count())->toEqual($count);
    $result->each(function (UserInfo $result) {
        expect($result->age)->toBeBetween(21, 40);
        expect($result->name)->toEqual('Eric Cartman');
        expect($result->email)->toEqual('eric@southparkcows.com');
        expect($result->birthday)->toBeInstanceOf(\DateTime::class);
    });
});

it('Can Make A Many New Classes With State Overrides via make()', function () {
    $factory = UserInfo::factory();
    $count = 5;
    $result = $factory->count($count)->make(['name' => 'Eric Cartman', 'email' => 'eric@southparkcows.com']);
    expect($result->count())->toEqual($count);
    $result->each(function (UserInfo $result) {
        expect($result->age)->toBeBetween(21, 40);
        expect($result->name)->toEqual('Eric Cartman');
        expect($result->email)->toEqual('eric@southparkcows.com');
        expect($result->birthday)->toBeInstanceOf(\DateTime::class);
    });
});

it('Can Make A New Class via Factory With State Methods', function () {
    $factory = UserInfo::factory()->restrictedAge();
    $result = $factory->make();
    expect($result->age)->toBeBetween(0, 12);
    expect($result->name)->toBeString();
    expect(Str::of($result->email)->contains('@'))->toBeTrue();
    expect($result->birthday)->toBeBetween(new DateTime('-12 years'), new DateTime);
});

it('It Can Resolve The Base Classes From The Factories', function (string $factoryClassName, string $expectedClassName) {
    $className = app($factoryClassName)->className();
    expect($className)->toEqual($expectedClassName);
})->with([
    'UserInfoFactory' => [UserInfoFactory::class, UserInfo::class],
    'ProfileDataFactory' => [ProfileDataFactory::class, ProfileData::class],
]);

it('It Can Resolve The Factory Class From The Base Classes', function (string $className, string $expectedFactoryClassName) {
    $factory = $className::factory()::resolveFactoryName($className);
    expect($factory)->toEqual($expectedFactoryClassName);
})->with([
    'UserInfo' => [UserInfo::class, UserInfoFactory::class],
    'ProfileData' => [ProfileData::class, ProfileDataFactory::class],
]);

it('It Can Set A Custom Resolver For Guessing Class Names', function (string $className) {
    $factory = $className::factory();
    $factory->guessClassNamesUsing(fn ($factory) => $className);
    expect($factory->className())->toEqual($className);
    $factory->guessClassNamesUsing(null);
})->with([
    'UserInfo' => [UserInfo::class],
    'ProfileData' => [ProfileData::class],
]);
