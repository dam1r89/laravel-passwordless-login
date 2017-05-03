<?php

namespace dam1r89\PasswordlessAuth;

use Illuminate\Database\Schema\Blueprint;
use dam1r89\PasswordlessAuth\User;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase($this->app);

    }


    protected function setUpDatabase($app)
    {
        // Setup users table
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
        });

        // Package migration
        $this->artisan('migrate');
    }
    
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup User provider
        $app['config']->set('passwordless.provider', User::class);

    }


    protected function getPackageProviders($app)
    {
        return [
            PasswordlessAuthServiceProvider::class,
        ];
    }
}
