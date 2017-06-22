# Laravel Passwordless LogIn

`composer require dam1r89/passwordless-auth`

Publish config

	php artisan vendor:publish --tag=passwordless


User must implement `dam1r89\PasswordlessAuth\Contracts\UsersProvider` contract or if user is instance of eloquent model just use `CanLoginWithEmail` trait.

	use dam1r89\PasswordlessAuth\CanLoginWithEmail;
	use dam1r89\PasswordlessAuth\Contracts\UsersProvider;

	class User extends SparkUser implements UsersProvider
	{
	    use CanLoginWithEmail;

And run migration

	php artisan migrate


## Visual part

Publish basic views which you can edit in `resources/views/vendor/passwordless`:

	php artisan vendor:publish --tag=passwordless-views

Template for sign-in form and for email is there.


TODO

- [ ] Add more tests
- [x] Add migration
- [x] Configurable
    - [x] Repository
	- [x] Route Prefix
	- [x] Email Content
	- [x] Signup timeout throttle
- [ ] Trait for user
- [x] Login or Login plus Sign Up
- [ ] Remove create user logic from broker
- [x] Publishing
	- [x] Emails
	- [x] config
- [ ] Write docs
