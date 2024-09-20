<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;

class UserInfoFactory extends UniversalFactory
{
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
        return $this->state(function (array $attributes) {
            return $attributes;
        })->afterMaking(fn (UserInfo $exampleClass) => $exampleClass->age = $age);
    }

    public function unrestrictedAge(): self
    {
        return $this->afterMaking(function (UserInfo $exampleClass) {
            $birthday = fake()->dateTimeBetween('now', '-21 years');
            $exampleClass->birthday = $birthday;
            $exampleClass->age = (new \DateTime)->diff($birthday)->y;
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
