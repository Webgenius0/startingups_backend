<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPrice extends Model
{
    use HasFactory;
    protected $table = 'business_prices';

    protected $fillable = [
        'business_profile_id',
        'type',
        'amount',
        'offerings'
    ];


    // relation with business profile
    public function businessProfile()
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    // hidden attributes
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
