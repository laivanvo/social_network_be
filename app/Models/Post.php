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
        'onlyme' => 'Only me',
        'friends' => 'Friends'
    ];

    public static function getAudiences()
    {
        return self::$audiences;
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include newest post
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNewestPosts($query)
    {
        $query->where('display', 1)->orderBy('created_at', 'desc');
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
}