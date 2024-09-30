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
            'externalId' => fn () => substr(str_replace(['+', '.', 'E'], '', microtime(false)), -10),
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'birthday' => $this->faker->dateTime,
            'age' => $this->faker->numberBetween(21, 40),
            'profileData' => ProfileData::factory(),
        ];
    }

    public function configure(): static
    {
        $this->afterMaking(fn (UserInfo $userInfo) => $userInfo->profileData = ProfileData::factory()->withProfileFor($userInfo)->make());

        return $this;
    }

    public function unrestrictedAge(): self
    {
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
