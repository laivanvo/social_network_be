<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Post;
use App\Models\FileUpload;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\Relation;


class RelationshipController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $relation = $this->currentUser()->requestToMes();
        if (count($relation->get())) {
            $relation = $relation->where('from', $id)->first();
            if ($relation) {
                return response()->json([
                    'type' => $relation->type,
                    'success' => 'send request successfully.'
                ]);
            }
        }
        $relation = $this->currentUser()->requestByMes();
        if (count($relation->get())) {
            $relation = $relation->where('to', $id)->first();
            if ($relation) {
                return response()->json([
                    'type' => $relation->type,
                    'success' => 'send request successfully.'
                ]);
            }
        }
        return response()->json([
            'type' => 'none',
            'success' => 'send request successfully.'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listRequest()
    {
        $from = $this->currentUser()->requestToMes()->where('type', 'request')->pluck('from');
        $relations = Profile::whereIn('user_id', $from)->get();
        return response()->json([
            'relations' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function listFriend()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from');
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to');
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $relations = Profile::whereIn('user_id', $from)->get();
        return response()->json([
            'relations' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function listFriendByAddress()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from');
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to');
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $relations = Profile::whereIn('user_id', $from)->where('address', $this->currentUser()->profile->address)->get();
        return response()->json([
            'relations' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function listFriendByBirthday()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from');
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to');
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $relations = Profile::whereIn('user_id', $from)->where('birthday', $this->currentUser()->profile->birthday)->get();
        return response()->json([
            'relations' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function sendRequest($id, Request $request)
    {
        $type = '';
        if ($request->type == 'add') {
            Relation::create([
                'from' => $this->currentUser()->id,
                'to' => $id,
                'type' => 'request',
            ]);
            $type = 'request';
        } else {
            $relation = Relation::Where('to', $id);
            $relation->delete();
            $type = 'none';
        }
        return response()->json([
            'type' => $type,
            'success' => 'send request successfully.'
        ]);
    }

    public function response($id, Request $request)
    {
        $type = '';
        if ($request->type == 'accept') {
            $relation = Relation::where('from', $id)->where('to', $this->currentUser()->id)->first();
            $relation->type = 'friend';
            $relation->save();
        } else {
            $relation = Relation::where('from', $id)->where('to', $this->currentUser()->id)->first();
            $relation->delete();
        }
        return response()->json([
            'success' => 'response successfully.'
        ]);
    }

    public function update($id, Request $request)
    {

        // $request->validate([
        //     'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048'
        // ]);

        $post = Post::find($id);
        $post->user_id = $this->currentUser()->id;
        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $post->file = '/storage/' . $file_path;
            $post->type = substr($request->file->getClientMimeType(), 0, 5);
        } else {
            $post->type = 'text';
        }
        $post->audience = $request->audience;
        $post->text = $request->text;
        $post->bg_image = $request->bg;
        $post->save();
        return response()->json([
            'post' => $post,
            'success' => 'create post successfully.'
        ]);
    }


    public function getFile()
    {
        $images = FileUpload::where('type', 'image')->get();
        $videos = FileUpload::where('type', 'video')->get();
        return response()->json([
            'images' => $images,
            'videos' => $videos,
        ]);
    }


    public function indexPersonal()
    {
        $user = $this->currentUser();
        $posts = $user->posts()->with(['user'])->get();
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser()
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->currentUser()->posts()->findOrFail($id)->delete();
        return response()->json(['success' => 'create post successfully.']);
    }

    /**
     * Display a listing of friends's posts
     *
     * @return \Illuminate\Http\Response
     */
    public function getFriendPosts()
    {
        $friendIds = $this->currentUser()->friends()->pluck(['id']);
        $friendPosts = Post::whereIn('user_id', $friendIds)
            ->isPublic()
            ->newestPosts()
            ->with(['profile', 'reactions'])
            ->paginate($this->paginationNum);
        if ($friendPosts->count() > 0) {
            foreach ($friendPosts as $row) {
                $row->audience = Post::getAudienceValue($row->audience);
            }
            return view('app.post.posts', ['posts' => $friendPosts]);
        }
        return response()->json(['message' => 'Max'], 200);
    }
}
