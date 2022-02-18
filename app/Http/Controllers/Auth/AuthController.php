<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show form login
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     */
    public function authenticate(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            if (Auth::User()->profile == null) {
                Profile::create([
                    'user_id' => Auth::User()->id
                ]);
                // dd(Auth::User()->profile->id);
                return redirect()->route('profile.edit');
            } else {
                // dd(Auth::User()->profile);
                if (Auth::User()->profile->first_name == ''
                || Auth::User()->profile->last_name == ''
                || Auth::User()->profile->phone_number == ''
                || Auth::User()->profile->birthday == ''
                ) {
                    return redirect()->route('profile.edit');
                } else {
                    return view('welcome');
                }
            }
        } else {
            return back()->withInput();
        }
    }

    /**
     * Show form register
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * Register an account.
     *
     */
    public function registerAccount(RegisterRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        if ($user->save()) {
            Auth::login($user);
            return redirect(route('home'));
        } else {
            return back()->withInput();
        }
    }
}
