<?php

return [
    /**
     * Route prefix for sign-in/sign-up form.
     */
    'route_prefix' => 'passwordless',

    /**
     * This is a model to which LoginToken is saving reference to.
     * Almost always this will be User class.
     */
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