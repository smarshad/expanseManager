<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class ExportRoutes extends Command
{
    protected $signature = 'routes:export {path=routes.csv}';
    protected $description = 'Export all routes to CSV file';

    public function handle()
    {
        $routes = Route::getRoutes();

        $file = fopen($this->argument('path'), 'w');

        fputcsv($file, ['METHOD', 'URI', 'NAME', 'ACTION', 'MIDDLEWARE']);

        foreach ($routes as $route) {
            fputcsv($file, [
                implode('|', $route->methods()),
                $route->uri(),
                $route->getName(),
                $route->getActionName(),
                implode('|', $route->gatherMiddleware()),
            ]);
        }

        fclose($file);

        $this->info("Routes exported to {$this->argument('path')}");

        return 0;
    }
}
