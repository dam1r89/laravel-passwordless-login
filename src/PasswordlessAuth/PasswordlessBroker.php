<?php

namespace dam1r89\PasswordlessAuth;

use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use dam1r89\PasswordlessAuth\Contracts\UsersProvider;

class PasswordlessBroker
{
    private $users;
    private $mail;

    // TODO: Add Login Token Repository
    public function __construct(UsersProvider $users, Mailer $mail)
    {
        $this->users = $users;
        $this->mail = $mail;
    }

    private function checkIfTokenShouldBeResent($email)
    {

        // If token doesn't exists or if it is send in
        // last 10 minutes to avoid abuse
        $token = LoginToken::whereEmail($email)->first();
        return is_null($token) || Carbon::now()->subMinutes(10)->gt($token->created_at);
    }

    public function sendLoginLink($email, $intendedUrl = null)
    {
        $user = $this->users->retrieveByEmail($email);
        if (is_null($user)) {
            return false;
        }

        if (!$this->checkIfTokenShouldBeResent($email)) {
            // TODO: Check if intended url should be updated here
            return true;
        }

        $token = LoginToken::create([
            'email' => $email,
            'intended_url' => $intendedUrl
        ]);

        $this->mail->send('passwordless::email.link', compact('token'), function (Message $mail) use ($user) {
            $mail->to($user->email)
                ->subject('Here is your sign in link.');
        });

        return true;
    }

    public function loginOrRegister($email, $intendedUrl)
    {
        $user = $this->users->retrieveByEmail($email);

        if (is_null($user)) {
            $this->users->newQuery()->forceCreate([
                'email' => $email,
                'name' => '',
                'password' => bcrypt(str_random(64))
            ]);
        }

        return $this->sendLoginLink($email, $intendedUrl);
    }

    public function getLogin($token)
    {
        // Login can be used only once, after first access
        // it is removed.
        $login = LoginToken::whereToken($token)->first();
        $copy = clone $login;
        $login->delete();

        return $copy;
    }
    
}
