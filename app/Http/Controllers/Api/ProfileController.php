<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\Relation;

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
        $from = $this->currentUser()->requestToMes()->where('type', 'request')->pluck('from')->toArray();
        $profiles = Profile::where('user_id', '<>', $this->currentUser()->id)->whereNotIn('user_id', $from)->get();
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


    public function search(Request $request)
    {
        $profiles = Profile::where('last_name', 'LIKE', '%' . $request->text . '%')->orWhere('first_name', 'LIKE', '%' . $request->text . '%')->get();
        return response()->json([
            'profiles' => $profiles,
            'success' => 'successfully.'
        ]);
    }

    public function searchOfFriend(Request $request)
    {

        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from')->toArray();
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to')->toArray();
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }

        $profiles = Profile::where('last_name', 'LIKE', '%' . $request->text . '%')
            ->orWhere('first_name', 'LIKE', '%' . $request->text . '%')->whereIn('user_id', $from)->get();
        return response()->json([
            'profiles' => $profiles,
            'success' => 'successfully.'
        ]);
    }
}
