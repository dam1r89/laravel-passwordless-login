<?php
namespace dam1r89\PasswordlessAuth\Contracts;

interface UsersProvider {

	public function retrieveByEmail($email);
}