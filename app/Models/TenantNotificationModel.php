<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantNotificationModel extends Model
{
    use HasFactory;
    protected $table = 'tenant_notifications';
    
    protected $fillable = ['title','message','tenant_id','message_language','pm_company_id','property_manager_id'];


}
