<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingModel extends Model
{
    use HasFactory;
    protected $table = 'buildings';
    protected $guarded = [];

}
