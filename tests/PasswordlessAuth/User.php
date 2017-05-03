<?php

namespace dam1r89\PasswordlessAuth;

use dam1r89\PasswordlessAuth\Contracts\UsersProvider;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements
	UsersProvider,
	AuthenticatableContract
{

    use Authenticatable;

	public function retrieveByEmail($email)
	{
		return $this->newQuery()->whereEmail($email)->first();
	}
}