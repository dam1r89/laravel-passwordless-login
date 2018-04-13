<?php

namespace dam1r89\PasswordlessAuth\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use dam1r89\PasswordlessAuth\PasswordlessBroker;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PasswordlessController extends Controller
{
    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use ValidatesRequests;

    public function form()
    {
        return view('passwordless::login');
    }

    public function login(Request $request, PasswordlessBroker $broker)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $email = $request->get('email');

        try {
            $broker->loginOrRegister($email, session('url.intended'));

            return redirect()->back()->with('status', __('We have e-mailed your sign in link!'));
        } catch (\Exception $e) {
            return redirect()->back()->with('status', $e->getMessage());
        }
    }

    public function auth(Request $request, PasswordlessBroker $broker)
    {
        $login = $broker->getLogin($request->get('token'));

        if (is_null($login)) {
            return redirect()->route('passwordless.login')->with('status', __('Token not found or expired, please request new sign in link.'));
        }

        Auth::login($login->user, config('passwordless.remember'));

        return redirect($login->intended_url ?: $this->redirectUrl());
    }

    protected function redirectUrl()
    {
        return config('passwordless.redirect_to', '/home');
    }
}
