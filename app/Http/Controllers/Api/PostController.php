<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Post;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use App\Models\BgImage;
use App\Models\User;

class PostController extends ApiController
{
    public function getProfile() {
        return response()->json([
            'success' => true,
            'profile' => $this->currentUser()->profile,
        ], 200);
    }
    public function index()
    {
        $posts = Post::with([
            'user', 'user.profile',
        ])
            ->where('group_id', -1)
            ->orderby('id', 'desc')
            ->paginate(5);
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $this->currentUser()->profile,
        ], 200);
    }

    public function listPostGroup()
    {
        $myGroups = $this->currentUser()->groups()->get()->pluck('id')->toArray();
        $posts = Post::with([
            'user', 'user.profile',
        ])
            ->whereIn('group_id', $myGroups)
            ->orderby('id', 'desc')
            ->take(5);
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $this->currentUser()->profile,
        ], 200);
    }

    public function indexPersonal()
    {
        $posts = $this->currentUser()->posts()->with([
            'user', 'user.profile',
        ])
            ->where('group_id', -1)
            ->orderby('id', 'desc')
            ->paginate(2);
        $profile = $this->currentUser()->profile;
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $profile,
        ], 200);
    }

    public function indexByPerson($id)
    {
        $posts = User::findOrFail($id)->posts()->with([
            'user', 'user.profile',
        ])
            ->where('group_id', -1)
            ->orderby('id', 'desc')
            ->get();
        $profile = $this->currentUser()->profile;
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $profile,
        ], 200);
    }

    public function indexGroup($id)
    {
        $posts = $this->currentUser()->posts()->with([
            'user', 'user.profile',
        ])
            ->where('group_id', $id)
            ->orderby('id', 'desc')
            ->paginate(5);
        $profile = $this->currentUser()->profile;
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $profile,
        ], 200);
    }

    public function upload(Request $request)
    {

        // $request->validate([
        //     'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048'
        // ]);

        $fileUpload = new BgImage;

        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $fileUpload->name = $request->file->getClientOriginalName();
            $fileUpload->path = '/storage/' . $file_path;
            $fileUpload->type = substr($request->file->getClientMimeType(), 0, 5);
            $fileUpload->save();
            return response()->json(['success' => 'File uploaded successfully.']);
        }
    }

    public function store(Request $request)
    {

        // $request->validate([
        //     'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048'
        // ]);

        $post = new Post();
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
        $post->count_comment = 0;
        $post->count_reaction = 0;
        $post->group_id = $request->group_id;
        $post->save();
        $post = Post::with(['user', 'user.profile',])->findOrFail($post->id);
        return response()->json([
            'success' => 'create post successfully.',
            'post' => $post,
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
        $post = Post::with(['user', 'user.profile',])->findOrFail($post->id);
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
