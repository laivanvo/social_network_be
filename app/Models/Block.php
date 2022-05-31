<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\User;

class Block extends Model
{
    protected $fillable = ['user_id', 'post_id'];
    public function post() {
        return $this->belongTo(Post::class);
    }

    public function user() {
        return $this->belongTo(User::class);
    }
}
