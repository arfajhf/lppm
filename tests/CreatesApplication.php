<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        if ($app['config']->get('database.default') !== 'mysql'
            || $app['config']->get('database.connections.mysql.database') !== 'lppm_test'
            || $app['config']->get('database.connections.mysql.username') !== 'lppm_test') {
            throw new \RuntimeException('Tests require the isolated MySQL lppm_test database and lppm_test account. Application data must not be used.');
        }

        return $app;
    }
}
