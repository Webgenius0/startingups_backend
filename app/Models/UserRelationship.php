<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRelationship extends Model
{
    use HasFactory;

    protected $table = 'user_relationships';

    protected $fillable = ['follower_id', 'followee_id'];


    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

     public function followee()
    {
        return $this->belongsTo(User::class, 'followee_id');
    }
}
