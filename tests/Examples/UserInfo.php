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

    public static function newFactory(): UserInfoFactory
    {
        return UserInfoFactory::new();
    }
}
