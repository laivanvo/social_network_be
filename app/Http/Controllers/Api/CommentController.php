<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Exceptions\ErrorException;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Profile;
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
            $comments = Comment::with(['user', 'user.profile'])
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
        $profile = Profile::where('user_id', $request->user_id)->first();
        if ($request->type != 'post') {
            $post_id = -1;
            $post_idz = $request->post;
            $comment_id = $request->comment_id;
            $comment_idz = $request->comment_id;
            $content = 'user ' . $profile->first_name . ' commented on your post';
            $commentz = Comment::findOrFail($request->comment_id);
            $commentz->count_rep++;
            $commentz->save();
        } else {
            $post_id = $request->post_id;
            $post_idz = $request->post_id;
            $comment_id = -1;
            $comment_idz = -1;
            $content = 'user ' . $profile->first_name . ' commented on your post';
            $post = Post::findOrFail($request->post_id);
            $post->count_comment++;
            $post->save();
        }
        $comment = new Comment();
        $comment->user_id = $this->currentUser()->id;
        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $comment->file = '/storage/' . $file_path;
            $comment->type = substr($request->file->getClientMimeType(), 0, 5);
        } else {
            $comment->type = 'text';
        }
        $comment->post_id = $post_id;
        $comment->previous_id = $comment_id;
        $comment->text = $request->text;
        $comment->count_rep = 0;
        $comment->count_reaction = 0;
        $comment->save();
        $comment = Comment::with(['user', 'user.profile'])->OrderBy('id', 'desc')->first();
        if ($request->user_id != $this->currentUser()->id) {
            Notification::create([
                'from' => $this->currentUser()->id,
                'to' => $request->user_id,
                'post_id' => $post_idz,
                'comment_id' => $comment_idz,
                'content' => $content,
            ]);
        }
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
