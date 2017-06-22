<?php

return [
	/**
	 * Route prefix for sign-in/sign-up form.
	 */
	'route_prefix' => 'passwordless',

	// TODO: Check if this is required
	'provider' => \App\User::class,

	/**
	 * Number of minutes user must wait before receiving new sign-up link.
	 */
	'throttle' => 60 * 10,

	/**
	 * Should user be automatically signed-up if email is not found
	 * in database.
	 */
	'sign_up' => true,
];