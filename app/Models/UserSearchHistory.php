<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSearchHistory extends Model
{
    use HasFactory;

    protected $table = 'user_search_histories';

    protected $fillable = [
        'user_id',
        'query',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
