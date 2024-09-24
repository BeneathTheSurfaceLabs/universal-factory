<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Support;

use Archetype\Drivers\InputInterface;
use Archetype\Support\PHPFileStorage;

class ArchetypeInputDriver implements InputInterface
{
    public ?string $filename = '';

    public ?string $extension = '';

    public ?string $relativeDir = '';

    public ?string $absoluteDir = '';

    /**
     * @throws \Exception
     */
    public function load(?string $path = null)
    {
        try {
            $reflection = new \ReflectionClass($path);
            $classFilePath = $reflection->getFileName();
            $this->absoluteDir = dirname($classFilePath);
            $this->filename = $reflection->getShortName();
            $this->extension = pathinfo($classFilePath, PATHINFO_EXTENSION);

            return (new PHPFileStorage)->get($classFilePath);
        } catch (\ReflectionException $e) {
            throw new \Exception("Archetype Input Driver: Class {$path} could not be found.");
        }
    }
}
