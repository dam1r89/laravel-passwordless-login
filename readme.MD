## Laravel Passwordless Auth

**Features**

- Routes are predefined (default is `/passwordless/login`)
- Sign-in and/or sign-up mode
- Sends sign-in email
- Send mail throttling

To get started, install package with composer:

	composer require dam1r89/passwordless-auth

Register the `dam1r89\PasswordlessAuth\PasswordlessAuthServiceProvider` provider in `config/app.php` configuration file.

	dam1r89\PasswordlessAuth\PasswordlessAuthServiceProvider::class,


Publish configuration.

	php artisan vendor:publish --tag=passwordless

Configuration file:

	/*
	 * Route prefix for sign-in/sign-up form.
	 */
	'route_prefix' => 'passwordless',

	/*
	 * This is a model to which LoginToken is saving reference to.
	 * Almost always this will be User class.
	 */
	'provider' => \App\User::class,

	/*
	 * Number of seconds user must wait before receiving new sign-up link.
	 */
	'throttle' => 60 * 10,

	/*
	 * Should user be automatically signed-up if email is not already used
	 */
	'sign_up' => true,

	/*
	 * Default redirect to
	 * Redirect url after user is signed in and intended url is not set
	 */
	'redirect_to' => 'home',

	/*
	 * If user should be "remembered" after sign-in.
	 */
	'remember' => true,

User must implement `dam1r89\PasswordlessAuth\Contracts\UsersProvider` contract or if user is instance of eloquent model just use `UsersRepository` trait.

	use dam1r89\PasswordlessAuth\UsersRepository;
	use dam1r89\PasswordlessAuth\Contracts\UsersProvider;

	class User extends SparkUser implements UsersProvider
	{
	    use UsersRepository;

... and run migration

	php artisan migrate

## Using passwordless as a default sign-in method

### For Laravel < 5.5 

In `/app/Exceptions/Handler.php` change `return redirect()->guest(route('login'));` to:

	return redirect()->guest(route('passwordless.login'));

### For Larvel 5.5+

Since Laravel 5.5 the `unauthenticated()` function in `/app/Exceptions/Handler.php` has moved to `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php`.  

You can still use this method but you have to override it.  In your `app/Exceptions/Handler.php` file include:

```php
use Request;
use Illuminate\Auth\AuthenticationException;
use Response;
```

and add the `unauthenticated` function:

```php
    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        return redirect()->guest(route('passwordless.login'));
    }
```


## Visual

Publish views and email template.

	php artisan vendor:publish --tag=passwordless-views

Views are located under `resources/views/vendor/passwordless` folder.

To replace current login link with passwordless use this route name:

	<a href="{{ route('passwordless') }}">Login</a>

## Passwordless Links

Sometimes you want to send a link via email that will automatically sign-in user. You can do that with `PasswordlessLink` class. Exemple for notifications.

	use dam1r89\PasswordlessAuth\PasswordlessLink;
	
	$link = PasswordlessLink::for($notifiable)->url('/route/to/resource');

**Note:** It is dangerous to forward emails with unused sign-in links because link gives direct access to account. 

### Similar package

[Laravel Passwordless Auth](https://github.com/Whyounes/laravel-passwordless-auth) 
