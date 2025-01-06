<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'day',
        'date',
        'is_closed',
        'open_time',
        'close_time',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function business_profile()
    {
        return $this->belongsTo(BusinessProfile::class);
    }
}
