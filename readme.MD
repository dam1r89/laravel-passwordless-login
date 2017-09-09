## Laravel Passwordless Auth

**Features**

- Routes are configured (default /passwordless/login)
- Email template
- Sign-in and/or sign-up mode
- Send mail throttling

`composer require dam1r89/passwordless-auth`

Add provider in `config/app.php`:

	dam1r89\PasswordlessAuth\PasswordlessAuthServiceProvider::class,


Publish config

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

## Redirection users to passwordless sign-in form:

Modify `/app/Exceptions/Handler.php`. Change `return redirect()->guest(route('login'));` to:

	return redirect()->guest(route('passwordless.login'));


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

**Note:** this can be dangerous if link is not used and email is forwarded.

### Similar package

[Laravel Passwordless Auth](https://github.com/Whyounes/laravel-passwordless-auth) 
