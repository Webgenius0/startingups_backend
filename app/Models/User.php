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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
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

    public function followers()
    {
        return $this->hasMany(UserRelationship::class, 'followee_id', 'id');
    }

    public function followees()
    {
        return $this->hasMany(UserRelationship::class, 'follower_id', 'id');
    }

    // Check if the user follows another user
    public function isFollowing($userId)
    {
        return $this->followees()->where('followee_id', $userId)->exists();
    }

    // Follow another user
    public function follow($userId)
    {
        if (!$this->isFollowing($userId)) {
            $this->followees()->create(['followee_id' => $userId]);
        }
    }

    // Unfollow another user
    public function unfollow($userId)
    {
        $this->followees()->where('followee_id', $userId)->delete();
    }

}
