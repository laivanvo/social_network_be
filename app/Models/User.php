<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Notification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function requestedRelations()
    {
        return $this->belongsToMany(User::class, 'relations', 'user_id', 'friend_id')->using(Relation::class)->withTimestamps();
    }

    public function requestingRelations()
    {
        return $this->belongsToMany(User::class, 'relations', 'friend_id', 'user_id')->using(Relation::class)->withTimestamps();
    }
    //lay ra tat ca cac thang duoc minh gui di
    public function requestedUsers()
    {
        return $this->requestedRelations()->wherePivot('type', 'request')->get();
    }
    //lay ra tat ca cac thang user gui request den minh
    public function requestingUsers()
    {
        return $this->requestingRelations()->wherePivot('type', 'request')->get();
    }

    public function friends()
    {
        return $this->requestedRelations()->wherePivot('type', 'friend')->get()
            ->merge($this->requestingRelations()->wherePivot('type', 'friend')->get());
    }

    //lay ra tat ca cac request ma minh gui di cho cac thang khac
    public function requestUsers()
    {
        return $this->requestedUsers()->merge($this->requestingUsers());
    }

    public function addFriend($friendId)
    {
        $this->requestedRelations()->attach($friendId, ['type' => 'request']);
    }

    public function isAddSuccess($friendId)
    {
        return count($this->requestedRelations()->where('user_id', $this->id)->where('friend_id', $friendId)->get()) == 1;
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

    public function scopeOpenAdd($query)
    {
        $query->whereRelation('setting', 'is_add_friend', 1);
    }
    /**
     * Check user is exist in db or not
     */
    public function isExistUser($id)
    {
        return self::find($id);
    }

    public function isFriendable($id)
    {
        $user = self::isExistUser($id);
        return $user
            && $id != $this->id
            && $this->friends()->where('id', $id)->count() == 0
            && $this->requestUsers()->where('id', $id)->count() == 0
            && $user->setting->is_add_friend == 1;
    }

    public function getRequestingRelation($id)
    {
        return $this->requestingRelations()->wherePivot('user_id', $id)->wherePivot('type', 'request')->first()->pivot;
    }

    public function isRequestedBy($id)
    {
        $user = self::isExistUser($id);
        return $user
            && $id != $this->id
            && $this->getRequestingRelation($id);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function sendRemindVerifyEmailNotification()
    {
        $this->notify(new RemindVerifyEmail);
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class, "user_id_from", "id");
    }
}
