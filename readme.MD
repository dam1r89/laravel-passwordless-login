## Laravel Passwordless Auth

`composer require dam1r89/passwordless-auth`

Publish config

	php artisan vendor:publish --tag=passwordless


User must implement `dam1r89\PasswordlessAuth\Contracts\UsersProvider` contract or if user is instance of eloquent model just use `UsersRepository` trait.

	use dam1r89\PasswordlessAuth\UsersRepository;
	use dam1r89\PasswordlessAuth\Contracts\UsersProvider;

	class User extends SparkUser implements UsersProvider
	{
	    use UsersRepository;

... and run migration

	php artisan migrate

## Redirection users to passwordless sign-i	n form:

Modify `/app/Exceptions/Handler.php`. Change `return redirect()->guest(route('login'));` to:

	return redirect()->guest(route('passwordless.login'));


## Visual

Publish views and email template.

	php artisan vendor:publish --tag=passwordless-views

Views are located under `resources/views/vendor/passwordless` folder.

