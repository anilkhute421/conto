<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableUnitImageModel extends Model
{
    use HasFactory;

    protected $table = 'available_unit_image';
    protected $guarded = [];


    public function getImageNameAttribute($value)
    {
        return \Config::get('constants.image_base_url').'/'.$value ; 
    }
    
}
