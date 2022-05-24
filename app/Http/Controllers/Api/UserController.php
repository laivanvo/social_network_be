<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Post;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use App\Models\Relation;
use App\Models\User;

class UserController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $relation = Relation::all();
        return response()->json([
            'relation' => $relation,
            'success' => 'send request successfully.'
        ]);
    }

    public function getImages($id) {
        $images = User::findOrFail($id)->posts()->where('type', 'image')->pluck('file')->toArray();
        return response()->json([
            'images' => $images,
            'success' => 'send request successfully.'
        ]);
    }

    public function getVideos($id) {
        $videos = User::findOrFail($id)->posts()->where('type', 'video')->pluck('file')->toArray();
        return response()->json([
            'videos' => $videos,
            'success' => 'send request successfully.'
        ]);
    }

    public function send(Request $request)
    {
        $relation = Relation::create([
            'from' => $request->from,
            'to' => $request->to,
            'type' => 'request',
        ]);
        return response()->json([
            'relation' => $relation,
            'success' => 'send request successfully.'
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
