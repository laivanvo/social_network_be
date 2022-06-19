<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'group_id',
        'text',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
