<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmFeddbackModel extends Model
{

    protected $table = 'property_manager_feedback';
    protected $guarded = [];

    
    use HasFactory;
}
