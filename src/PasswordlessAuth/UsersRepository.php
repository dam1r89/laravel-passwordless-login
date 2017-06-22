<?php

namespace dam1r89\PasswordlessAuth;

trait UsersRepository
{
    public function retrieveByEmail($email)
    {
        return $this->newQuery()->whereEmail($email)->first();
    }
    
    public function createWithEmail($email)
    {
        $this->newQuery()->forceCreate([
            'email' => $email,
            'name' => '',
            'password' => bcrypt(str_random(64))
        ]);
    }
}