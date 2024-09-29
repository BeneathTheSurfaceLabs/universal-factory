<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\Enum\ClassConstructionStrategy;
use BeneathTheSurfaceLabs\UniversalFactory\UniversalFactory;
use Illuminate\Support\Str;

class ProfileDataFactory extends UniversalFactory
{
    protected ClassConstructionStrategy $classConstructionStrategy = ClassConstructionStrategy::ARRAY_BASED;

    /**
     * Define the class's default attributes.
     *
     * @return array<string, mixed>
     */
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
