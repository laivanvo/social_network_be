<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Http\Request;


class GroupController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $myGroup = $this->currentUser()->groups()->get()->pluck('id')->toArray();
        if (!count($myGroup)) {
            $myGroup = [];
        }
        $member = $this->currentUser()->members()->get()->pluck('group_id')->toArray();
        if (!count($member)) {
            $member = [];
        }
        for ($i = 0; $i < count($member); $i++) {
            array_push($myGroup, $member[$i]);
        }
        $groups = Group::with('user', 'user.profile')->whereNotIn('id', $myGroup)->get();
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

    public function listOfMeCurrent()
    {
        $myGroups = $this->currentUser()->groups()->with('user', 'user.profile')->take(10)->get();
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
        $avatar = "";
        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $avatar = '/storage/' . $file_path;
        }
        $group = $this->currentUser()->groups()->create([
            'name' => $request->name,
            'audience' => $request->audience,
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
