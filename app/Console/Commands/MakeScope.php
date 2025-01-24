<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeScope extends Command
{
    protected $signature = 'make:scope {model} {name}';

    protected $description = 'Add a new local scope to a model';

    public function handle()
    {
        $model = $this->argument('model');
        $scopeName = $this->argument('name');

        $modelPath = app_path("Models/{$model}.php");

        if (! file_exists($modelPath)) {
            $this->error("Model {$model} not found!");

            return;
        }

        $scopeMethod = <<<EOD

    /**
     * Scope a query to filter by {$scopeName}.
     */
    public function scope{$scopeName}(\$query, \$value)
    {
        // Add your query logic here
        return \$query;
    }
EOD;

        $content = file_get_contents($modelPath);
        $content = preg_replace('/\}(\s*)$/', "{$scopeMethod}\n}\$1", $content);

        file_put_contents($modelPath, $content);

        $this->info("Scope {$scopeName} added to model {$model}.");
    }
}
