<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory;
    // use SoftDeletes;


    protected $guarded = [];


    protected $casts = [
        'guest_list' => 'array',
        'guest_options' => 'array',
    ];

    // event prices
    public function event_prices()
    {
        return $this->hasMany(EventPrice::class);
    }

    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // event booking
    public function event_bookings()
    {
        return $this->hasMany(EventBooking::class);
    }


    // event clicks
    public function event_clicks()
    {
        return $this->hasMany(EventClick::class);
    }


    // event reviews
    public function event_reviews()
    {
        return $this->hasMany(EventReview::class);
    }
}
