<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintanceRequestForModel extends Model
{
    use HasFactory;

    protected $table = 'maitinance_request_for';
    protected $guarded = ['maitinance_request_name'];
}
