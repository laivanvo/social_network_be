<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProfileValidate;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiController;

class ProfileController extends ApiController
{
    public function index()
    {
        $profile = $this->currentUser()->profile()->first();
        return response()->json([
            'success' => true,
            'profile' => $profile,
        ], 200);
    }

    public function update(ProfileValidate $request)
    {
        $profile = $this->currentUser()->profile;
        if ($request->hasFile('avatar')
            && $profile->avatar
            && Storage::exists('images/'.$profile->avatar)
        ) {
            Storage::delete('images/'.$profile->avatar);
        }
        if ($request->hasFile('avatar')) {
            $imageName = time().'.'.$request->avatar->extension();
            $request->avatar->storeAs('images', $imageName, 'public');
        } else {
            $imageName = $profile->avatar;
        }
        $this->currentUser()->profile()->update([
            'first_name' => $request->firstname,
            'last_name' => $request->lastname,
            'phone_number' => $request->phone,
            'birthday' => $request->birthday,
            'address' => $request->address,
            'gender' => $request->gender,
            'avatar' => $imageName,
        ]);
        return $this->responseSuccess([
            'message' => 'Update successfully',
            'avatar' => $imageName,
        ]);
    }
}
