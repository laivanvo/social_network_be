<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Exceptions\ErrorException;
use App\Models\Reaction;
use Spatie\Valuestore\Valuestore;
use Illuminate\Http\Request;

class ReactionController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->type == 'post' ? 'App\Models\Post' : 'App\Models\Comment';
        $user = $this->currentUSer();
        $reaction_user = $user->reactions()
            ->where('reactiontable_id', $request->id)
            ->where('reactiontable_type', $type)
            ->get();
        $like = count($reaction_user) ? true : false;
        $reactions = Reaction::where('reactiontable_id', $request->id)
            ->where('reactiontable_type', $type)
            ->get();
        return response()->json([
            'success' => true,
            'reactions' => $reactions,
            'count_like' => count($reactions),
            'like' => $like,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $type = $request->type == 'post' ? 'App\Models\Post' : 'App\Models\Comment';
        $user = $this->currentUser();
        $reaction = Reaction::where('reactiontable_id', $request->id)
            ->where('user_id', $user->id)
            ->where('reactiontable_type', $type);
        if (!count($reaction->get())) {
            $reaction = $user->reactions()->create([
                'reactiontable_id' => $request->id,
                'reactiontable_type' => $type,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'like success',
            ], 200);
        } else {
            $reaction->delete();
            return response()->json([
                'success' => true,
                'message' => 'unlike success',
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
