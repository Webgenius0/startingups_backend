<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Billable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected $casts = [
        'preferences' => 'array',
    ];
    

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Define relationship with the BusinessProfile model.
     */
    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class);
    }

    /**
     * Users following this user (followers).
     */
    public function followers()
    {
        return $this->hasMany(UserRelationship::class, 'followee_id', 'id');
    }

    /**
     * Users this user is following (followees).
     */
    public function followees()
    {
        return $this->hasMany(UserRelationship::class, 'follower_id', 'id');
    }

    /**
     * Check if the user follows another user.
     */
    public function isFollowing($userId)
    {
        return $this->followees()->where('followee_id', $userId)->exists();
    }

    /**
     * Follow another user.
     */
    public function follow($userId)
    {
        if (!$this->isFollowing($userId)) {
            $this->followees()->create(['followee_id' => $userId]);
        }
    }

    /**
     * Unfollow another user.
     */
    public function unfollow($userId)
    {
        $this->followees()->where('followee_id', $userId)->delete();
    }

    /**
     * User search history.
     */
    public function user_search()
    {
        return $this->hasMany(UserSearchHistory::class, 'user_id', 'id');
    }

    public function user_preferences()
    {
        return $this->hasMany(UserPreference::class, 'user_id', 'id');
    }



}
