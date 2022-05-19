<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Group;

use function PHPSTORM_META\type;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'group_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
