<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintanceExpertModel extends Model
{
    use HasFactory;
    protected $table = 'maintenance_experts';
    protected $fillable = ['maintenance_id','expert_id','unique_code','visit_date_time'];
}
