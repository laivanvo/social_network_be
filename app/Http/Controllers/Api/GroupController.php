<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Http\Request;
use Faker\Factory;



class GroupController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $groups = Group::with('user', 'user.profile', 'members')->where('avatar', '<>', "")->get();
        return response()->json([
            'success' => true,
            'groups' => $groups,
        ], 200);
    }

    public function listOfMe()
    {
        $myGroups = $this->currentUser()->groups()->with('user', 'user.profile')->get();
        return response()->json([
            'success' => true,
            'groups' => $myGroups,
        ], 200);
    }

    public function listJoin()
    {
        $myGroups = $this->currentUser()->members()->pluck('group_id')->toArray();
        $groups = Group::whereIn('id', $myGroups)->get();
        return response()->json([
            'success' => true,
            'groups' => $groups,
        ], 200);
    }

    // public function listOfMe()
    // {
    //     $myGroups = $this->currentUser()->groups()->with('user', 'user.profile')->get();
    //     return response()->json([
    //         'success' => true,
    //         'groups' => $myGroups,
    //     ], 200);
    // }

    public function listOfMeCurrent()
    {
        $myGroups = Group::with('user', 'user.profile')->where('user_id', $this->currentUser()->id)->get();
        return response()->json([
            'success' => true,
            'groups' => $myGroups,
        ], 200);
    }

    public function listSend()
    {
        $mySends = Member::where('user_id', $this->currentUser()->id)->where('type', 'request')->get()->pluck('group_id');
        $groups = Group::with('user', 'user.profile')->whereIn('id', $mySends)->get();
        return response()->json([
            'success' => true,
            'groups' => $groups,
        ], 200);
    }

    public function listRequest($id)
    {
        $groups = Group::findOrFail($id)->members()->where('type', 'request');
        return response()->json([
            'success' => true,
            'groups' => $groups,
        ], 200);
    }

    public function show($id)
    {
        $group = Group::findOrFail($id)->with(['members', 'user', 'user.profile', 'members.user', 'members.user.profile'])->first();
        return response()->json([
            'success' => true,
            'group' => $group,
        ], 200);
    }

    public function store(Request $request)
    {
        $faker = Factory::create();
        $avatar = "";
        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $avatar = '/storage/' . $file_path;
        }
        $group = $this->currentUser()->groups()->create([
            'card' => $faker->phonenumber,
            'name' => $request->name,
            'audience' => $request->audience,
            'content' => $request->content,
            'bonus' => $request->bonus,
            'avatar' => $avatar,
        ]);
        $group = Group::with(['user', 'user.profile'])->findOrFail($group->id);
        return response()->json([
            'success' => true,
            'group' => $group,
        ], 200);
    }

    public function joinGroup($id)
    {
        Member::create([
            'group_id' => $id,
            'user_id' => $this->currentUser()->id,
            'type' => 'request',
        ]);
        $group = Group::with('user', 'user.profile')->findOrFail($id);
        return response()->json([
            'success' => true,
            'group' => $group,
        ], 200);
    }

    public function listMember($id)
    {
        $member = Group::findOrFail($id)->members()->get()->pluck('user_id')->toArray();
        $profiles = Profile::whereIn('user_id', $member)->get();
        return response()->json([
            'success' => true,
            'profiles' => $profiles,
        ], 200);
    }
}
