<?php

return [
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
];
