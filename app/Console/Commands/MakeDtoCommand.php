<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto {name}';
    protected $description = 'Create a new Data Transfer Object class';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        $directory = app_path('DTOs');
        $path = $directory . "/{$name}.php";

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($path)) {
            $this->error("DTO {$name} already exists!");
            return self::FAILURE;
        }

        $stub = <<<PHP
        <?php

        namespace App\DTOs;
        use Spatie\LaravelData\Data;
        
        class {$name}
        {
            public function __construct(){
        
            }
            // TODO: Add properties and constructor
        }

        PHP;

        File::put($path, $stub);

        $this->info("DTO {$name} created successfully at app/DTOs/{$name}.php");

        return self::SUCCESS;
    }
}
