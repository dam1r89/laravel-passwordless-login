<?php

namespace dam1r89\PasswordlessAuth;

class LoginTokenRepository
{
	public function findByEmail($email)
	{
		\DB::table('login_tokens')->where('email', $email)->first();
	}

	public function findByToken()
	{
		// body
	}

	public function create()
	{

	}
}