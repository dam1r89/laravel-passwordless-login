<?php

namespace dam1r89\PasswordlessAuth\Events;

class UserRegistered
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
