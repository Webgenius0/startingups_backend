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
}
