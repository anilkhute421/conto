<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyManagerCompany extends Model
{
    use HasFactory;
    protected $table = 'property_manager_companies';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'office_contact_no',
        'location',
        'country_id',
        'currency_id',
        'status',



    ];

}
