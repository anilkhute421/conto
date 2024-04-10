<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TenantModel  extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tenants';
    protected $fillable = ['first_name','last_name','email','password', 'email_url_key','status','is_phone_verify' ,'is_email_verify','phone','country_id','property_manager_id','building_id','unit_id','country_code','otp','os_type','language','address','os_version','device_token','app_version','unique_email_key','email_key_expire','pm_company_id','tenant_code'];

    // tenant table status
    // 0 - Pending Approval   req
    // 1 - Approved
    // 2- Declined    req
    // 3- Disconnected
}
