<?php

namespace dam1r89\PasswordlessAuth\Controllers;

use Auth;
use dam1r89\Events\UserLoggedIn;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use dam1r89\PasswordlessAuth\PasswordlessBroker;
use Illuminate\Foundation\Validation\ValidatesRequests;


class PasswordlessController extends Controller
{
    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use ValidatesRequests;

	protected $redirectTo = '/home';

    public function form()
    {
        return view('passwordless::login');
    }

    public function login(Request $request, PasswordlessBroker $broker)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $email = $request->get('email');

        $broker->loginOrRegister($email, session('url.intended'));

        return redirect()->back()->with('status', 'We have e-mailed your sign in link!');
    }

    public function auth(Request $request, PasswordlessBroker $broker)
    {

        $login = $broker->getLogin($request->get('token'));

        if (is_null($login)) {
            return redirect()->route('passwordless.login')->with('status', 'Token not found or expired, please request new sign in link.');
        }

        Auth::login($login->user);


        return redirect($login->intended_url ?: $this->redirectTo);
    }
}
