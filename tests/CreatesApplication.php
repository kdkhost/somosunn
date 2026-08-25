<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (!$app->environment('testing') || $database !== 'testing') {
            throw new \RuntimeException(
                "Testes bloqueados: ambiente ou banco inseguro (env={$app->environment()}, database={$database})."
            );
        }

        return $app;
    }
}
