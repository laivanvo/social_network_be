<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Group extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'audience',
        'avatar',
        'card',
        'content',
        'bonus',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasmany(Member::class);
    }

    public function Rules()
    {
        return $this->hasMany(Rule::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
