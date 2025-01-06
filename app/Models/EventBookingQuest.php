<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBookingQuest extends Model
{
    use HasFactory;

    protected $table = 'event_booking_quests';

    protected $fillable = [
        'event_booking_id',
        'full_name',
        'phone',
        'age',
    ];

    // hidden fields
    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    // relation with event booking
    public function eventBooking()
    {
        return $this->belongsTo(EventBooking::class);
    }



}
