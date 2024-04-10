<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialisteExpertId extends Model
{
    use HasFactory;
    protected $table = 'specialisties_expert_id';

    protected $fillable = ['expert_id','speciality_id'];


}
