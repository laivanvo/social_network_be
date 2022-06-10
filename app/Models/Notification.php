<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class Notification extends Model
{
    protected $fillable = ['from', 'to', 'post_id', 'comment_id', 'content'];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function post() {
        return $this->belongsTo(Post::class);
    }

    public function comment() {
        return $this->belongsTo(Comment::class);
    }

    public function userFrom()
    {
        return $this->belongsTo(User::class, 'from', 'id');
    }

    public function userTo()
    {
        return $this->belongsTo(User::class, 'to', 'id');
    }
}
