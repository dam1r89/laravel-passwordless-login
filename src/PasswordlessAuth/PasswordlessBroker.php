<?php

namespace dam1r89\PasswordlessAuth;

use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use dam1r89\PasswordlessAuth\Contracts\UsersProvider;
use dam1r89\PasswordlessAuth\Events\UserRegistered;

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

    private function checkIfTokenShouldBeSent($email)
    {
        // If token doesn't exists or if it is send in
        // last x seconds defined in config to avoid abuse
        $token = LoginToken::whereEmail($email)->first();

        return is_null($token) ||
            Carbon::now()->subSeconds(config('passwordless.throttle'))->gt($token->created_at);
    }

    public function sendLoginLink($email, $intendedUrl = null)
    {
        $user = $this->users->retrieveByEmail($email);
        if (is_null($user)) {
            throw new \Exception(__('User with e-mail :email not found', ['email'=>$email]));
        }

        if (!$this->checkIfTokenShouldBeSent($email)) {
            // TODO: Check if intended url should be updated here
            return true;
        }

        $token = LoginToken::firstOrNew([
            'email' => $email,
        ]);

        $token->fill([
            'intended_url' => $intendedUrl,
            'created_at' => Carbon::now(),
        ])->save();

        $this->mail->send('passwordless::email.link', compact('token'), function (Message $mail) use ($user) {
            $mail->to($user->email)
                ->subject(__('Here is your sign in link.'));
        });

        return true;
    }

    public function loginOrRegister($email, $intendedUrl)
    {
        $user = $this->users->retrieveByEmail($email);

        if (is_null($user) && config('passwordless.sign_up')) {
            // TODO: Create contract for this
            $user = $this->users->createWithEmail($email);
            event(new UserRegistered($user));
        }

        return $this->sendLoginLink($email, $intendedUrl);
    }

    public function getLogin($token)
    {
        // TODO: Add token lifetime

        // Login can be used only once, after first access
        // it is removed.
        $login = LoginToken::whereToken($token)->first();

        if (is_null($login)) {
            return $login;
        }

        $copy = clone $login;
        $login->delete();

        return $copy;
    }
}
