<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventClick extends Model
{
    use HasFactory;

    protected $table = 'event_clicks';

    protected $fillable = [
        'user_id',
        'business_profile_id',
        'last_click',
    ];
}
