<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBooking extends Model
{
    use HasFactory;

    protected $guarded = [];

    // hidden attributes
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   //business profile
    public function business_profile()
    {
        return $this->belongsTo(BusinessProfile::class);
    }



    // event booking guests
    public function guests()
    {
        return $this->hasMany(EventBookingQuest::class);
    }
    // payments

    public function payments(){
        return $this->hasOne(PaymentTransaction::class, 'event_booking_id', 'id');
    }
}
