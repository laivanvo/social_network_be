<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Post;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use App\Models\BgImage;
use App\Models\File;
use App\Models\Group;
use App\Models\User;
use App\Models\Relation;

class PostController extends ApiController
{
    public function getProfile()
    {
        return response()->json([
            'success' => true,
            'profile' => $this->currentUser()->profile,
        ], 200);
    }
    public function index()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from')->toArray();
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to')->toArray();
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        array_push($from, $this->currentUser()->id);
        $posts = Post::with([
            'files', 'user', 'user.profile', 'blocks'
        ])
            ->where('group_id', -1)
            ->orderby('id', 'asc')
            ->whereIn('user_id', $from)
            ->where('audience', 'public')
            ->paginate(5);
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $this->currentUser()->profile,
        ], 200);
    }

    public function listQueue($id)
    {
        $posts = Post::with(['user', 'user.profile', 'files', 'blocks'])->where('group_id', $id)->where('in_queue', 1)->get();
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $this->currentUser()->profile,
        ], 200);
    }

    public function listPostByGroup($id)
    {
        $group = Group::findOrFail($id);
        $posts = Post::with([
            'user', 'user.profile', 'blocks'
        ])
            ->where('group_id', $group)
            ->orderby('id', 'desc')
            ->paginate(5);
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
            'files', 'user', 'user.profile', 'blocks', 'group',
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
            'files', 'user', 'user.profile', 'blocks'
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
            'files', 'user', 'user.profile', 'blocks', 'group', 'group.user', 'group.user.profile'
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

    public function listPostGroup()
    {
        $from = $this->currentUser()->groups()->pluck('id')->toArray();
        $to = $this->currentUser()->members()->where('type', 'member')->pluck('group_id')->toArray();
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $posts = Post::with(['files', 'user', 'user.profile', 'blocks', 'group', 'group.user', 'group.user.profile'])->whereIn('group_id', $from)->get();
        return response()->json([
            'success' => true,
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $this->currentUser()->profile,
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

    public function offComment($id)
    {
        $post = Post::findOrFail($id);
        $post->off_comment = $post->off_comment == 0 ? 1 : 0;
        $post->save();
        return response()->json(['success' => 'File uploaded successfully.']);
    }

    public function store(Request $request)
    {

        // $request->validate([
        //     'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048'
        // ]);

        $post = new Post();
        $post->file = '';
        $post->user_id = $this->currentUser()->id;
        $post->audience = $request->audience;
        $post->text = $request->text;
        $post->count_comment = 0;
        $post->count_reaction = 0;
        $post->group_id = $request->group_id;
        $post->off_comment = $request->off_comment;
        $post->in_queue = $request->in_queue == 'false' ? false : true;
        $post->save();
        if ($request->file()) {
            for ($i = 0; $i < $request->count - 1; $i++) {
                $file = 'file' . $i;
                $file_name = time() . '_' . $request->$file->getClientOriginalName();
                $file_path = $request->file($file)->storeAs('uploads', $file_name, 'public');
                $post->files()->create([
                    'path' => '/storage/' . $file_path,
                    'type' => substr($request->$file->getClientMimeType(), 0, 5),
                ]);
            }
            $file = 'file' . ($request->count - 1);
            $file_name = time() . '_' . $request->$file->getClientOriginalName();
            $file_path = $request->file($file)->storeAs('uploads', $file_name, 'public');
            $post->files()->create([
                'path' => '/storage/' . $file_path,
                'type' => substr($request->$file->getClientMimeType(), 0, 5),
            ]);
        }
        $post = Post::with(['files', 'user', 'user.profile', 'blocks', 'group', 'group.user', 'group.user.profile'])->findOrFail($post->id);
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

        $post = Post::findOrFail($id);
        $post->file = '';
        $post->user_id = $this->currentUser()->id;
        $post->audience = $request->audience;
        $post->text = $request->text;
        $post->group_id = $request->group_id;
        $post->in_queue = $request->in_queue == 'false' ? false : true;
        $post->save();
        $post->files()->delete();
        if ($request->file()) {
            for ($i = 0; $i < $request->count - 1; $i++) {
                $file = 'file' . $i;
                $file_name = time() . '_' . $request->$file->getClientOriginalName();
                $file_path = $request->file($file)->storeAs('uploads', $file_name, 'public');
                $post->files()->create([
                    'path' => '/storage/' . $file_path,
                    'type' => substr($request->$file->getClientMimeType(), 0, 5),
                ]);
            }
            $file = 'file' . ($request->count - 1);
            $file_name = time() . '_' . $request->$file->getClientOriginalName();
            $file_path = $request->file($file)->storeAs('uploads', $file_name, 'public');
            $post->files()->create([
                'path' => '/storage/' . $file_path,
                'type' => substr($request->$file->getClientMimeType(), 0, 5),
            ]);
        }
        $post = Post::with(['files', 'user', 'user.profile', 'blocks', 'group', 'group.user', 'group.user.profile'])->findOrFail($post->id);
        return response()->json([
            'success' => 'create post successfully.',
            'post' => $post,
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

    public function accept($id)
    {
        $post = Post::findOrFail($id);
        $post->in_queue = 0;
        $post->save();
        return response()->json([
            'succes' => 'succes',
        ]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->saves()->delete();
        $post->delete();

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

    public function search(Request $request)
    {
        $posts = Post::with(['files', 'user', 'user.profile', 'blocks'])->where('text', 'LIKE', '%' . $request->text . '%')->get();
        return response()->json([
            'posts' => $posts,
            'user' => $this->currentUser(),
            'profile' => $this->currentUser()->profile,
            'message' => 'success'
        ]);
    }

    public function load($id) {
        $post = Post::with([
            'files', 'user', 'user.profile', 'blocks'
            ])->where('id', $id)->first();
        return response()->json([
            'post' => $post,
        ]);
    }
}
