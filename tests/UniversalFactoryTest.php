<?php

use BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfo;
use BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples\UserInfoFactory;
use Illuminate\Support\Str;

it('Can Create A New Factory Instance', function () {
    $factory = UserInfo::factory();
    expect($factory)->toBeInstanceOf(UserInfoFactory::class);
});

it('Can Make A New Class via Factory With Empty State', function () {
    $factory = UserInfo::factory();
    $result = $factory->make();

    expect($result->age)->toBeBetween(21, 40);
    expect($result->name)->toBeString();
    expect(Str::of($result->email)->contains('@'))->toBeTrue();
    expect($result->birthday)->toBeInstanceOf(\DateTime::class);
});

it('Can Make A Many New Classes via Factory With Empty State', function () {
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

it('Can Make A Many New Classes via Factory With Mixed State', function () {
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

it('Can Make A New Class via Factory With Mixed State', function () {
    $factory = UserInfo::factory(['name' => 'Eric Cartman', 'email' => 'eric@southparkcows.com']);
    $result = $factory->make();

    expect($result->age)->toBeBetween(21, 40);
    expect($result->name)->toEqual('Eric Cartman');
    expect($result->email)->toEqual('eric@southparkcows.com');
    expect($result->birthday)->toBeInstanceOf(\DateTime::class);
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
    "UserInfoFactory" => [UserInfoFactory::class, UserInfo::class],
]);

it('It Can Resolve The Factory Class From The Base Classes', function (string $className, string $expectedFactoryClassName) {
    $factory = $className::factory()::resolveFactoryName($className);
    expect($factory)->toEqual($expectedFactoryClassName);
})->with([
    "UserInfo" => [UserInfo::class, UserInfoFactory::class, ],
]);
