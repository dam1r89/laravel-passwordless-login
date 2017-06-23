<?php

namespace dam1r89\PasswordlessAuth;

use Illuminate\Database\Schema\Blueprint;
use dam1r89\PasswordlessAuth\User;
use Route;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
        $this->faker = \Faker\Factory::create();

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

    public function setUpRoutes($app)
    {
        $router = $app['router'];
        $router->get('protected', function(){
            return 'protected content';
        })->middleware('auth');

        $router->get('named/route', function(){
            return 'some random content';
        })->name('namedRoute');
        
        $router->getRoutes()->refreshNameLookups(); 
    }


    protected function getPackageProviders($app)
    {
        return [
            PasswordlessAuthServiceProvider::class,
        ];
    }
}
