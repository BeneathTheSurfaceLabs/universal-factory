<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Enum;

enum ClassConstructionStrategy
{
    case ARRAY_BASED;

    case CONTAINER_BASED;

    case REFLECTION_BASED;
}
