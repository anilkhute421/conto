<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintanceRequestModel extends Model
{
    use HasFactory;

    protected $table = 'maintance_requests';
    protected $fillable = ['property_manager_id','pm_company_id','tenant_id','building_id','unit_id','status','description','maintenance_request_id','request_code','preferred_date_time'];
    // status should be as below
    //-	Request raised (1)
    //- Request assigned (2)
    //-	Request completed (3)
    //-	Request is on hold (4)
    //-	Request canceled (5)

}
