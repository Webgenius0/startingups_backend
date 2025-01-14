<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessProfile extends Model
{

    use HasFactory;
    use SoftDeletes;


    protected $guarded = [];

    // protected $casts = [
    //     'is_closed' => 'boolean' ,
    // ];
    



    protected $hidden = [
        'created_at',
        'updated_at',
    ];




    public function business_hours()
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function business_prices()
    {
        return $this->hasMany(BusinessPrice::class);
    }

    // age limit
    public function age_limit()
    {
        return $this->hasMany(BusinessAgeLimit::class,);
    }

    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // event booking
    public function event_bookings()
    {
        return $this->hasMany(EventBooking::class, 'business_profile_id', 'id');
    }


    // event ratings
    public function event_reviews()
    {
        return $this->hasMany(EventReview::class);
    }


    // category name 
    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    // sub category name 
    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
