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
