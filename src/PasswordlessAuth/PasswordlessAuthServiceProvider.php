<?php
namespace dam1r89\PasswordlessAuth;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use dam1r89\PasswordlessAuth\Contracts\UsersProvider;


class PasswordlessAuthServiceProvider extends ServiceProvider
{

	// Intentionally not having Http, you can organize this however you want - really cool
    private $controllersNamespace = 'dam1r89\PasswordlessAuth\Controllers';

    private $routePrefix = 'passwordless';

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'passwordless');

        $this->publishes([__DIR__ . '/config.php' => config_path('passwordless.php')], 'passwordless');

        $this->routePrefix = $this->app['config']->get('passwordless.route_prefix');

        $this->app->bind(UsersProvider::class, function($app) {
            return $app->make($app['config']['passwordless.provider']);
        });
    }


    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->loadViewsFrom(__DIR__.'/views', 'passwordless');
        $this->app->call([$this, 'map']);
    }

    public function map(Router $router)
    {
        $router->group([
            'namespace' => $this->controllersNamespace,
            'middleware' => 'web',
            'prefix' => $this->routePrefix
        ], function () {
            require __DIR__ . '/routes.php';
        });
    }


}