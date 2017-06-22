<?php

namespace dam1r89\PasswordlessAuth;

use dam1r89\PasswordlessAuth\Contracts\UsersProvider;

trait CanLoginWithEmail
{
	public function retrieveByEmail($email)
	{
		return $this->newQuery()->whereEmail($email)->first();
	}
}