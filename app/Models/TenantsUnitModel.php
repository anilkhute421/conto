<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantsUnitModel extends Model
{
    use HasFactory;
    protected $table = 'tenants_units';
    protected $guarded = [];

}
