<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    private static $audiences = [
        'public' => 'Public',
        'private' => 'Private',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'audience',
        'text',
        'bg_image_id',
        'file',
        'type',
        'count_comment',
    ];

    public static function getAudiences()
    {
        return self::$audiences;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bgImage()
    {
        return $this->belongsTo(BgImage::class);
    }

    /**
     * Scope a query to only include newest post
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */

    public function scopeIsPublic($query)
    {
        $query->where('audience', 'public');
    }

    public static function getAudienceValue($audienceKey)
    {
        foreach (self::$audiences as $key => $value) {
            if ($audienceKey === $key) {
                return $value;
            }
        }
        return 'Public';
    }

    /**
     * Check audience
     */
    public function checkAudience($audience)
    {
        return in_array($audience, array_flip(self::$audiences));
    }

    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

    public function comments()
    {
        return $this->comment()->orderby('id', 'desc');
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactiontable');
    }

    public function getPostIsExitComment($id)
    {
        return $this->whereHas("comments", function ($query) use ($id) {
            $query->where("id", $id);
        })->first();
    }

    public function getPostIsExitReaction($id)
    {
        return $this->whereHas("reactions", function ($query) use ($id) {
            $query->where("id", $id);
        })->first();
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'user_id', 'user_id');
    }
}
