<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\LoginRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        $token = $this->auth()->attempt(['email' => $request->email, 'password' => $request->password]);
        if (!$token) {
            return $this->responseError(['message' => 'Unauthorized'], 401);
        }
        return $this->responseSuccess([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request)
    {
        $user = User::create([
            'name' => $request->first_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        Profile::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'address' => $request->address,
        ]);
        $token = $this->auth()->attempt(['email' => $request->email, 'password' => $request->password]);
        if (!$token) {
            return $this->responseError(['message' => 'Unauthorized'], 401);
        }
        return $this->responseSuccess([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth()->factory()->getTTL() * 60
        ]);
    }
}
