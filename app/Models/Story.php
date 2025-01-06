<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'cover',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function reviews()
    {
        return $this->hasMany(StoryReview::class);
    }


    public function likes()
    {
        return $this->hasMany(StoryLike::class);
    }



}
