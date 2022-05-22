<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Exceptions\ErrorException;
use App\Models\Comment;
use App\Models\Reaction;
use Spatie\Valuestore\Valuestore;
use Illuminate\Http\Request;


class CommentController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->type == 'post') {
            $comments = Comment::with(['user'])
            ->where('post_id', $request->id)
            ->orderBy('id', 'desc')
            ->paginate(2);
            $count = Comment::where('post_id', $request->id);
        } else {
            $comments = Comment::with(['user'])
            ->where('previous_id', $request->id)
            ->orderBy('id', 'desc')
            ->paginate(2);
            $count = Comment::where('previous_id', $request->id);
        }
        return response()->json([
            'success' => true,
            'count' => $count,
            'comments' => $comments,
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
        if ($request->type == 'post') {
            $this->currentUser()->comments()->create([
                'post_id' => $request->id,
                'previous_id' => -1,
                'text' => $request->text,
                'count_rep' => 0,
            ]);
            $post = Post::findOrFail($request->id);
            $post->count_comment++;
            $post->save();
        } else {
            $this->currentUser()->comments()->create([
                'post_id' => -1,
                'previous_id' => $request->id,
                'text' => $request->text,
                'count_rep' => 0,
            ]);
            $commentz = Comment::findOrFail($request->id);
            $commentz->count_rep++;
            $commentz->save();
        }
        $comment = Comment::OrderBy('id', 'desc')->with(['user'])->first();
        return response()->json([
            'success' => 'successfully.',
            'comment' => $comment,
        ]);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request  $request)
    {
        $comment = $this->currentUser()->comments()->findOrFail($id)->update($request->all());
        return response()->json([
            'success' => 'successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if ($request->type == 'post') {
            $post = Post::findOrFail($request->id);
            $post->count_comment--;
            $post->save();
        } else {
            $commentz = Comment::findOrFail($request->id);
            $commentz->count_rep--;
            $commentz->save();
        }
        $this->currentUser()->comments()->findOrFail($id)->delete();
        return response()->json([
            'success' => 'successfully',
        ]);
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
