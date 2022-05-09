<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Exceptions\ErrorException;
use App\Models\Reaction;
use Spatie\Valuestore\Valuestore;

class CommentController extends ApiController
{
    private $audiences = [];
    private $paginationNum = 0;
    private $levelParent = 1;

    public function __construct()
    {
        $this->middleware('verified');
        $this->audiences = Post::getAudiences();
        $settings = Valuestore::make(storage_path('app/settings.json'));
        $this->paginationNum = $settings->get('post_pagination', 0);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $comments = Post::find($id)->comments()->with(['user'])->get();
        return response()->json([
            'success' => true,
            'comments' => $comments,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        $user = $this->currentUser();
        $reaction = Reaction::where('reactiontable_id', $id)
            ->where('user_id', $user->id);

        if (!count($reaction->get())) {
            $reaction = $user->reactions()->create([
                'reactiontable_id' => $id,
                'reactiontable_type' => 'App\Models\Post',
            ]);
            return response()->json([
                'success' => true,
                'reaction' => $reaction,
                'like' => true,
            ], 200);
        } else {
            $reaction->delete();
            return response()->json([
                'success' => true,
                'message' => 'unlike success',
                'like' => false,
            ], 200);
        }
    }

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
    public function update(PostRequest $request, Post $post)
    {
        if (
            !Post::checkAudience($request->audience)
            || $post->user_id !== $this->currentUser()->id
        ) {
            throw new ErrorException();
        }
        $post->update([
            'content' => $request->content,
            'audience' => $request->audience,
        ]);
        return redirect()->route('posts.index');
    }

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
