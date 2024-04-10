<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantTempModel extends Model
{
    use HasFactory;

    protected $table = 'tenant_temp';
    
    protected $fillable = ['email','otp'];

}
