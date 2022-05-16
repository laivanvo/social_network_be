<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Exceptions\ErrorException;
use Spatie\Valuestore\Valuestore;
use App\Models\FileUpload;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use App\Models\BgImage;


class PostController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with([
            'user',
        ])
            ->orderby('id', 'desc')
            ->paginate(2);
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser()
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

        $post = new Post;
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
        $post->bg_image_id = $request->bg;
        $post->count_comment = 0;
        $post->save();
        return response()->json([
            'success' => 'create post successfully.',
            'post' => $post,
        ]);
    }

    public function update(Request $request)
    {

        // $request->validate([
        //     'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048'
        // ]);

        $post = Post::find($request->id);
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
        $post->bg_image_id = $request->bg;
        $post->save();
        return response()->json(['success' => 'create post successfully.']);
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
