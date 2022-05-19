<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Auth\Events\Registered;

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
     */
    public function authenticate(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            if ($this->currentUser()->profile == null) {
                Profile::create([
                    'user_id' => $this->currentUser()->id,
                    'first_name' => '',
                    'last_name' => '',
                    'phone_number' => '',
                    'gender' => '',
                    'birthday' => '1900-01-01',
                    'address' => ''
                ]);
                return redirect()->route('home.index');
            } else {
                $user = $this->currentUser()->profile;
                if ($user->first_name == ''
                || $user->last_name == ''
                || $user->phone_number == ''
                || $user->birthday == ''
                ) {
                    return redirect()->route('home.index');
                } else {
                    return redirect()->route('home.index');
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
     */
    public function registerAccount(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        Auth::login($user);
        event(new Registered($user));
        $user->setting()->create([
            'is_noti' => 1,
            'is_add_friend' => 1,
        ]);
        return redirect()->route('home.index');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
