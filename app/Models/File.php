<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['post_id', 'type', 'path'];
    public function post()
    {
        return $this->belongTo(Post::class);
    }
}
