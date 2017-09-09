<?php

namespace dam1r89\PasswordlessAuth;

use Carbon\Carbon;

class PasswordlessLink
{
    private $forUser;

    private function __construct($forUser)
    {
        $this->forUser = $forUser;
    }

    public static function for($user)
    {
        return new static($user);
    }

    public function url($url)
    {
        return $this->create(url($url));
    }

    public function route()
    {
        return $this->create(call_user_func_array('route', func_get_args()));
    }

    private function create($url)
    {
        $token = LoginToken::firstOrNew([
            'intended_url' => $url,
            'email' => $this->forUser->email,
        ]);

        $token->created_at = Carbon::now();

        $token->save();

        return $token->getLoginLink();
    }
}
