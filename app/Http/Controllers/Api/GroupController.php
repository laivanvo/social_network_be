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
    public function index()
    {
        $groups = Group::with('user', 'user.profile')->where('user->id', '>', -1)->get();
        return response()->json([
            'success' => true,
            'groups' => $groups,
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
        $group = Group::findOrFail($group->id)->with('user', 'user.profile');
        return response()->json([
            'success' => true,
            'groups' => $group,
        ], 200);
    }

    public function joinGroup($id)
    {
        Member::create([
            'group_id' => $id,
            'user_id' => $this->currentUser()->id,
            'type' => 'request',
        ]);
    }
}
