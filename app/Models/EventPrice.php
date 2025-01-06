<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPrice extends Model
{
    use HasFactory;

    protected $table = 'event_prices';

    protected $fillable = [
        'event_id',
        'type',
        'amount',
        'offerings',
    ];


    // hidden fields
    protected $hidden = [
        'created_at',
        'updated_at',
    ];



    public function event()
    {
        return $this->belongsTo(Event::class);
    }


}
