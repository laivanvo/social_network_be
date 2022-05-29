<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Profile;
use Illuminate\Http\Request;

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

    public function list()
    {
        $profiles = Profile::where('user_id' , '<>', $this->currentUser()->id)->get();
        return response()->json([
            'success' => true,
            'profiles' => $profiles,
        ], 200);
    }

    public function update(Request $request)
    {
        $profile = $this->currentUser()->profile()->first();
        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $profile->avatar = '/storage/' . $file_path;
        }
        $profile->first_name = $request->first_name;
        $profile->last_name = $request->last_name;
        $profile->phone_number = $request->phone_number;
        $profile->birthday = $request->birthday;
        $profile->address = $request->address;
        $profile->gender = $request->gender;
        $profile->save();
        return response()->json([
            'profile' => $profile,
            'success' => 'update profile successfully.'
        ]);
    }
}
