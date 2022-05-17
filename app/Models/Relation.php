<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'from',
        'to',
        'type',
    ];

    public function userFrom()
    {
        return $this->belongsTo(User::class, 'from', 'id');
    }

    public function userTo()
    {
        return $this->belongsTo(User::class, 'to', 'id');
    }
}
