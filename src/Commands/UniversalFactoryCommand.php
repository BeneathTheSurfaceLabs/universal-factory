<?php

namespace BeneathTheSurfaceLabs\UniversalFactory\Commands;

use Illuminate\Console\Command;

class UniversalFactoryCommand extends Command
{
    public $signature = 'make:universal-factory ';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
