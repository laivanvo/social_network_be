<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class group extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'audience',
    ];
}
