<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Tests\Examples;

use BeneathTheSurfaceLabs\UniversalFactory\Traits\HasUniversalFactory;

class UserInfo
{
    use HasUniversalFactory;

    public function __construct(
        public string $name,
        public string $email,
        public \DateTime $birthday,
        public int $age,
    ) {}

    public static function newFactory(): UserInfoFactory
    {
        return UserInfoFactory::new();
    }
}
