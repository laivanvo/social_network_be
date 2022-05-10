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
            'bgImage',
            'comments',
            'comments.user',
            'comments.replies',
            'comments.replies.user',
            'comments.replies.replies',
            'comments.replies.replies.user',
            'comments.reactions',
            'comments.reactions.user',
            'comments.replies.reactions',
            'comments.replies.reactions.user',
            'comments.replies.replies.reactions',
            'comments.replies.replies.reactions.user',
            'reactions',
            'reactions.user',
        ])
            ->orderby('id', 'desc')
            ->get();
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
        $post->save();
        return response()->json(['success' => 'create post successfully.']);
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
    public function create()
    {
        return view('app.post-create-update', ['user' => $this->currentUser(), 'audiences' => $this->audiences]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function show(Post $post)
    {
        $post->audience = Post::getAudienceValue($post->audience);
        return view("app.detail-post", [
            'post' => $post,
            'user' => $this->currentUser(),
            'paginationNum' => $this->paginationNum,
            'levelParent' => $this->levelParent,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('app.post-create-update', [
            'user' => $this->currentUser(),
            'audiences' => $this->audiences,
            'post' => $post
        ]);
    }

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
    public function destroy(Post $post)
    {
        if ($post->user_id != $this->currentUser()->id) {
            throw new ErrorException();
        }
        $post->delete();
        return redirect(route("posts.index"));
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
