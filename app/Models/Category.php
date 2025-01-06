<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;


    protected $fillable = ['name','type','image', 'gender_type'];

    protected $hidden =['created_at' , 'updated_at'];

    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class);
    }

}
