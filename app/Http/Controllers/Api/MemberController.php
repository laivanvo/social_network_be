<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Models\Profile;


class MemberController extends ApiController
{
    public function listMember($id)
    {
        $member = Group::findOrFail($id)->members()->where('type', 'member')->get()->pluck('user_id')->toArray();
        $profiles = Profile::whereIn('user_id', $member)->get();
        return response()->json([
            'success' => true,
            'profiles' => $profiles,
        ], 200);
    }

    public function listRequest($id)
    {
        $member = Group::findOrFail($id)->members()->where('type', 'request')->get()->pluck('user_id')->toArray();
        $profiles = Profile::whereIn('user_id', $member)->get();
        return response()->json([
            'success' => true,
            'profiles' => $profiles,
        ], 200);
    }

    public function accept(Request $request) {
        $member = Member::where('group_id', $request->group_id)->where('user_id', $request->user_id)->first();
        $member->type = 'member';
        $member->Save();
        return response()->json([
            'success' => true,
        ], 200);
    }

    public function destroy(Request $request)
    {
        $member = Member::where('group_id', $request->group_id)->where('user_id', $request->user_id)->first();
        $member->delete();
        return response()->json([
            'success' => true,
        ], 200);
    }
}
