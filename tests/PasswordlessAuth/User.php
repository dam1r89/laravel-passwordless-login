<?php

namespace dam1r89\PasswordlessAuth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use dam1r89\PasswordlessAuth\Contracts\UsersProvider;
use dam1r89\PasswordlessAuth\UsersRepository;

class User extends Model implements
	UsersProvider,
	AuthenticatableContract
{

    use Authenticatable, UsersRepository;


}