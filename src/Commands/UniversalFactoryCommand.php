<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Commands;

use Illuminate\Console\Command;

class UniversalFactoryCommand extends Command
{
    protected $signature = 'make:universal-factory {factoryClass} {--for=}';
    protected $description = 'Generate a universal factory class for a given class and modify the class to use it';

    public function handle()
    {
        dd(app()->rootNamespace());
        $factoryClass = $this->argument('factoryClass');
        $forClass = $this->option('for');
        $namespace = config('universal-factory.default_namespace');

        if (!$forClass) {
            $this->error('You must specify the --for= option with the class the factory is for.');
            return;
        }

        $this->generateFactoryClass($factoryClass, $forClass, $namespace);

        $this->info("Universal Factory for {$forClass} has been created successfully.");
    }

    protected function generateFactoryClass($factoryClass, $forClass, $namespace)
    {
        $path = app_path(str_replace('\\', '/', $namespace) . "/{$factoryClass}.php");

        if (file_exists($path)) {
            $this->error("Factory {$factoryClass} already exists at {$path}!");
            return;
        }

        // Stub file for the factory class
        $stub = $this->getStub();
        $stub = str_replace('{{ factoryClass }}', $factoryClass, $stub);
        $stub = str_replace('{{for}}', $forClass, $stub);
        $stub = str_replace('$namespace', $namespace, $stub);

        // Write the generated stub content to the factory file
        file_put_contents($path, $stub);

        $this->info("Factory {$factoryClass} created successfully at {$path}.");
    }

    protected function getStub()
    {
        return file_get_contents(__DIR__ . '/stubs/universal-factory.stub');
    }

    protected function getClassFilePath($className)
    {
        try {
            $reflection = new \ReflectionClass($className);
            return $reflection->getFileName();
        } catch (\ReflectionException $e) {
            $this->error("Class {$className} could not be found.");
            return null;
        }
    }
}
