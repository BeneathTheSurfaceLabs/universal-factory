<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\Traits\HasUniversalFactory;

class ProfileData
{
    use HasUniversalFactory;

    public function __construct(
        public ?string $facebookProfileUrl,
        public ?string $facebookAvatarUrl,
        public ?string $twitterProfileUrl,
        public ?string $twitterAvatarUrl,
        public ?string $gitHubProfileUrl,
        public ?string $githubAvatarUrl,
        public ?string $personalUrl,
    ) {}
}
